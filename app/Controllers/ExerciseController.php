<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Policy;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Collection;
use App\Models\Exercise;
use App\Models\Language;

final class ExerciseController extends Controller
{
    private const QUIZ_TTL = 3600; // mashq tokeni 1 soat amal qiladi

    public function index(): void
    {
        $user = Auth::user();
        [$sort, $dir] = $this->sortParams(Exercise::SORTS, 'created', 'desc');
        $filters = [
            'q'           => mb_substr($this->query('q'), 0, 100),
            'language_id' => $this->queryInt('language_id'),
            'type'        => $this->query('type'),
            'sort'        => $sort,
            'dir'         => $dir,
        ];
        $this->view('exercises/index', [
            'title'       => 'Mashqlar',
            'description' => 'Variantli testlar va yozma tarjima mashqlari orqali so‘z boyligingizni tekshiring.',
            'filters'     => $filters,
            'result'      => Exercise::search($filters, $user, $this->page()),
            'languages'   => Language::options(),
            'user'        => $user,
        ]);
    }

    public function show(int $id): void
    {
        $user = Auth::user();
        $ex = $this->visible($id, $user);
        $this->view('exercises/show', [
            'title'    => $ex['title'],
            'ex'       => $ex,
            'canEdit'  => Policy::canEditCollection($ex, $user),
            'attempts' => $user ? Exercise::userAttempts((int) $user['id'], $id, 10) : [],
            'user'     => $user,
        ]);
    }

    public function create(): void
    {
        $user = $this->requireAuth();
        $collections = Collection::editableBy($user);
        if (!$collections) {
            Session::flash('info', 'Mashq yaratish uchun avval o‘zingizning to‘plamingiz bo‘lishi kerak.');
            $this->redirect('/collections/create');
        }
        $this->view('exercises/form', [
            'title' => 'Yangi mashq', 'noindex' => true,
            'ex' => ['collection_id' => $this->queryInt('collection_id'), 'type' => 'choice', 'direction' => 'to_uz', 'question_count' => 10],
            'collections' => $collections, 'errors' => [],
        ]);
    }

    public function store(): void
    {
        $user = $this->requireAuth();
        $this->verifyCsrf();
        [$data, $errors] = $this->validate($user);
        if ($errors) {
            $this->view('exercises/form', ['title' => 'Yangi mashq', 'noindex' => true, 'ex' => $data, 'collections' => Collection::editableBy($user), 'errors' => $errors], 422);
            return;
        }
        $id = Exercise::create($data, (int) $user['id']);
        Session::flash('success', 'Mashq yaratildi.');
        $this->redirect('/exercises/' . $id);
    }

    public function edit(int $id): void
    {
        $user = $this->requireAuth();
        $ex = $this->editable($id, $user);
        $this->view('exercises/form', ['title' => 'Mashqni tahrirlash', 'noindex' => true, 'ex' => $ex, 'collections' => Collection::editableBy($user), 'errors' => []]);
    }

    public function update(int $id): void
    {
        $user = $this->requireAuth();
        $this->verifyCsrf();
        $this->editable($id, $user);
        [$data, $errors] = $this->validate($user);
        if ($errors) {
            $this->view('exercises/form', ['title' => 'Mashqni tahrirlash', 'noindex' => true, 'ex' => $data + ['id' => $id], 'collections' => Collection::editableBy($user), 'errors' => $errors], 422);
            return;
        }
        Exercise::update($id, $data);
        Session::flash('success', 'Mashq yangilandi.');
        $this->redirect('/exercises/' . $id);
    }

    public function destroy(int $id): void
    {
        $user = $this->requireAuth();
        $this->verifyCsrf();
        $ex = $this->editable($id, $user);
        Exercise::delete($id);
        Session::flash('success', '«' . $ex['title'] . '» mashqi o‘chirildi.');
        $this->redirect('/collections/' . $ex['collection_id']);
    }

    public function play(int $id): void
    {
        $user = $this->requireAuth();
        $ex = $this->visible($id, $user);
        if ($ex['status'] !== 'active') {
            abort(403, 'Arxivlangan to‘plam mashqini bajarib bo‘lmaydi.');
        }
        $minWords = $ex['type'] === 'choice' ? 2 : 1;
        if ((int) $ex['words_count'] < $minWords) {
            Session::flash('error', 'Mashq uchun to‘plamda yetarli so‘z yo‘q.');
            $this->redirect('/exercises/' . $id);
        }
        $questions = Exercise::buildQuiz($ex);

        // Savollar ro‘yxati serverda saqlanadi; token bir marta ishlatiladi (takroriy yuborish rad etiladi)
        $token = bin2hex(random_bytes(16));
        $quizzes = array_filter(
            (array) Session::get('_quiz', []),
            static fn($q) => is_array($q) && ($q['started'] ?? 0) > time() - self::QUIZ_TTL
        );
        $quizzes = array_slice($quizzes, -4, null, true);
        $quizzes[$token] = ['exercise_id' => $id, 'words' => array_column($questions, 'word_id'), 'started' => time()];
        Session::set('_quiz', $quizzes);

        $this->view('exercises/play', ['title' => $ex['title'], 'noindex' => true, 'ex' => $ex, 'questions' => $questions, 'token' => $token]);
    }

    public function submit(int $id): void
    {
        $user = $this->requireAuth();
        $this->verifyCsrf();
        $ex = $this->visible($id, $user);

        $token = $this->input('quiz_token');
        $quizzes = (array) Session::get('_quiz', []);
        $quiz = $quizzes[$token] ?? null;
        if (!is_array($quiz) || (int) $quiz['exercise_id'] !== $id || $quiz['started'] < time() - self::QUIZ_TTL) {
            Session::flash('error', 'Bu mashq natijasi allaqachon qabul qilingan yoki muddati tugagan. Mashqni qaytadan boshlang.');
            $this->redirect('/exercises/' . $id);
        }
        unset($quizzes[$token]);
        Session::set('_quiz', $quizzes);

        $answers = [];
        foreach ((array) ($_POST['answers'] ?? []) as $wordId => $value) {
            if (is_string($value)) {
                $answers[(int) $wordId] = $value;
            }
        }
        try {
            $res = Exercise::saveAttempt($ex, (int) $user['id'], array_map('intval', $quiz['words']), $answers, (int) $quiz['started']);
        } catch (\DomainException $e) {
            Session::flash('error', $e->getMessage());
            $this->redirect('/exercises/' . $id);
        }
        if ($res['moved'] > 0) {
            Session::flash('info', "Xato javob berilgan {$res['moved']} ta so‘z qaytadan o‘rganishga o‘tkazildi va bugungi takrorlashga qo‘shildi.");
        }
        $this->redirect('/attempts/' . $res['attempt_id']);
    }

    /** Natija sahifasi: faqat urinish egasi yoki administrator ko‘ra oladi */
    public function attempt(int $id): void
    {
        $user = $this->requireAuth();
        $a = Exercise::attempt($id);
        if (!$a || ((int) $a['user_id'] !== (int) $user['id'] && $user['role'] !== 'admin')) {
            abort(404);
        }
        $this->view('exercises/result', [
            'title'   => 'Mashq natijasi',
            'noindex' => true,
            'a'       => $a,
            'answers' => Exercise::attemptAnswers($id),
        ]);
    }

    private function visible(int $id, ?array $user): array
    {
        $ex = Exercise::find($id);
        if (!$ex || !Policy::canViewCollection($ex, $user)) {
            abort(404);
        }
        return $ex;
    }

    private function editable(int $id, array $user): array
    {
        $ex = $this->visible($id, $user);
        if (!Policy::canEditCollection($ex, $user)) {
            abort(403, 'Mashqni faqat to‘plam egasi yoki administrator o‘zgartira oladi.');
        }
        return $ex;
    }

    private function validate(array $user): array
    {
        $data = [
            'collection_id'  => (int) $this->input('collection_id'),
            'title'          => $this->input('title'),
            'type'           => $this->input('type'),
            'direction'      => $this->input('direction'),
            'question_count' => (int) $this->input('question_count'),
        ];
        $v = (new Validator($_POST))
            ->required('collection_id', 'To‘plam')
            ->required('title', 'Nomi')->length('title', 'Nomi', 3, 150)
            ->required('type', 'Turi')->in('type', 'Turi', array_keys(Exercise::TYPES))
            ->required('direction', 'Yo‘nalish')->in('direction', 'Yo‘nalish', array_keys(Exercise::DIRECTIONS))
            ->required('question_count', 'Savollar soni')->intBetween('question_count', 'Savollar soni', 3, 30);
        $c = $data['collection_id'] > 0 ? Collection::find($data['collection_id']) : null;
        $v->rule('collection_id', $c !== null && $c['status'] === 'active' && Policy::canEditCollection($c, $user), 'Tanlangan to‘plam mavjud emas yoki unga mashq qo‘shish huquqingiz yo‘q.');
        return [$data, $v->errors()];
    }
}
