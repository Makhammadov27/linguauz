<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Policy;
use App\Core\Session;
use App\Core\Uploader;
use App\Core\Validator;
use App\Models\Collection;
use App\Models\Exercise;
use App\Models\File;
use App\Models\Language;
use App\Models\Review;
use App\Models\Word;

final class CollectionController extends Controller
{
    public function index(): void
    {
        $user = Auth::user();
        [$sort, $dir] = $this->sortParams(Collection::SORTS, 'created', 'desc');
        $filters = [
            'q'           => mb_substr($this->query('q'), 0, 100),
            'language_id' => $this->queryInt('language_id'),
            'level'       => $this->query('level'),
            'mine'        => $user && $this->query('mine') === '1' ? 1 : 0,
            'archived'    => $user && $this->query('archived') === '1' ? 1 : 0,
            'sort'        => $sort,
            'dir'         => $dir,
        ];
        $this->view('collections/index', [
            'title'       => 'So‘z to‘plamlari',
            'description' => 'Daraja (A1–C2) va til bo‘yicha saralangan so‘z to‘plamlari: kundalik so‘zlashuv, sayohat, IT atamalari va boshqalar.',
            'filters'     => $filters,
            'result'      => Collection::search($filters, $user, $this->page()),
            'languages'   => Language::options(),
            'user'        => $user,
        ]);
    }

    public function show(int $id): void
    {
        $user = Auth::user();
        $c = $this->visible($id, $user);
        $canEdit = Policy::canEditCollection($c, $user);
        $this->view('collections/show', [
            'title'       => $c['title'],
            'description' => mb_substr((string) ($c['description'] ?: $c['title'] . ' — ' . $c['language_name'] . ' so‘zlari to‘plami.'), 0, 160),
            'noindex'     => !$c['is_public'],
            'c'           => $c,
            'words'       => Word::forCollection($id),
            'files'       => File::forCollection($id),
            'exercises'   => Exercise::forCollection($id),
            'canEdit'     => $canEdit,
            'inPlan'      => $user ? Review::inPlanCount((int) $user['id'], $id) : 0,
            'learners'    => $canEdit ? Collection::learnersCount($id) : 0,
            'user'        => $user,
            'uploadRules' => Uploader::RULES,
        ]);
    }

    public function create(): void
    {
        $user = $this->requireAuth();
        $this->view('collections/form', [
            'title' => 'Yangi to‘plam', 'noindex' => true,
            'c' => ['level' => 'A1', 'is_public' => Policy::canPublish($user) ? 1 : 0],
            'languages' => Language::options(), 'canPublish' => Policy::canPublish($user), 'errors' => [],
        ]);
    }

    public function store(): void
    {
        $user = $this->requireAuth();
        $this->verifyCsrf();
        [$data, $errors] = $this->validate($user, (int) $user['id']);
        if ($errors) {
            $this->view('collections/form', ['title' => 'Yangi to‘plam', 'noindex' => true, 'c' => $data, 'languages' => Language::options(), 'canPublish' => Policy::canPublish($user), 'errors' => $errors], 422);
            return;
        }
        $id = Collection::create($data, (int) $user['id']);
        Session::flash('success', 'To‘plam yaratildi. Endi unga so‘zlar qo‘shing.');
        $this->redirect('/collections/' . $id);
    }

    public function edit(int $id): void
    {
        $user = $this->requireAuth();
        $c = $this->editable($id, $user);
        $this->view('collections/form', ['title' => 'To‘plamni tahrirlash', 'noindex' => true, 'c' => $c, 'languages' => Language::options(), 'canPublish' => Policy::canPublish($user), 'errors' => []]);
    }

    public function update(int $id): void
    {
        $user = $this->requireAuth();
        $this->verifyCsrf();
        $c = $this->editable($id, $user);
        [$data, $errors] = $this->validate($user, (int) $c['owner_id'], $id, (int) $c['is_public']);
        if ($errors) {
            $this->view('collections/form', ['title' => 'To‘plamni tahrirlash', 'noindex' => true, 'c' => $data + ['id' => $id], 'languages' => Language::options(), 'canPublish' => Policy::canPublish($user), 'errors' => $errors], 422);
            return;
        }
        Collection::update($id, $data);
        Session::flash('success', 'To‘plam ma’lumotlari yangilandi.');
        $this->redirect('/collections/' . $id);
    }

    /**
     * O‘chirish qoidasi: agar to‘plamdan boshqa foydalanuvchilar o‘rganayotgan bo‘lsa (takrorlash rejasi
     * yoki mashq natijalari mavjud), ularning tarixi yo‘qolmasligi uchun o‘chirish o‘rniga ARXIVLASH qo‘llanadi.
     */
    public function destroy(int $id): void
    {
        $user = $this->requireAuth();
        $this->verifyCsrf();
        $c = $this->editable($id, $user);
        $learners = Collection::learnersCount($id, (int) $c['owner_id']);
        if ($learners > 0 && $this->input('force') !== '1') {
            Collection::setStatus($id, 'archived');
            Session::flash('info', "To‘plamdan {$learners} nafar boshqa foydalanuvchi o‘rganmoqda, shuning uchun u o‘chirilmadi, balki arxivlandi va faol ro‘yxatdan chiqarildi.");
            $this->redirect('/collections', ['archived' => 1]);
        }
        if ($learners > 0 && $user['role'] !== 'admin') {
            abort(403, 'O‘rganuvchilari bor to‘plamni butunlay o‘chirishni faqat administrator bajara oladi.');
        }
        foreach (Collection::delete($id) as $stored) {
            Uploader::remove($stored);
        }
        Session::flash('success', '«' . $c['title'] . '» to‘plami va undagi barcha so‘zlar, mashqlar, fayllar o‘chirildi.');
        $this->redirect('/collections');
    }

    public function restore(int $id): void
    {
        $user = $this->requireAuth();
        $this->verifyCsrf();
        $c = $this->editable($id, $user);
        Collection::setStatus($id, 'active');
        Session::flash('success', '«' . $c['title'] . '» to‘plami arxivdan qaytarildi.');
        $this->redirect('/collections/' . $id);
    }

    /** O‘rganishni boshlash: to‘plam so‘zlari foydalanuvchining takrorlash rejasiga qo‘shiladi */
    public function start(int $id): void
    {
        $user = $this->requireAuth();
        $this->verifyCsrf();
        $c = $this->visible($id, $user);
        if ($c['status'] !== 'active') {
            abort(403, 'Arxivlangan to‘plamni o‘rganishni boshlab bo‘lmaydi.');
        }
        if ((int) $c['words_count'] === 0) {
            Session::flash('error', 'Bu to‘plamda hali so‘z yo‘q.');
            $this->redirect('/collections/' . $id);
        }
        $added = Review::startCollection((int) $user['id'], $id);
        Session::flash($added > 0 ? 'success' : 'info', $added > 0
            ? "{$added} ta so‘z takrorlash rejangizga qo‘shildi. Birinchi takrorlashni hoziroq boshlang!"
            : 'Bu to‘plamning barcha so‘zlari allaqachon rejangizda.');
        $this->redirect('/review/session', ['collection_id' => $id]);
    }

    private function visible(int $id, ?array $user): array
    {
        $c = Collection::find($id);
        if (!$c || !Policy::canViewCollection($c, $user)) {
            abort(404);
        }
        return $c;
    }

    private function editable(int $id, array $user): array
    {
        $c = $this->visible($id, $user);
        if (!Policy::canEditCollection($c, $user)) {
            abort(403, 'Siz faqat o‘zingiz yaratgan to‘plamni o‘zgartira olasiz.');
        }
        return $c;
    }

    /** @return array{0:array,1:array} */
    private function validate(array $user, int $ownerId, int $exceptId = 0, int $currentPublic = 0): array
    {
        $data = [
            'title'       => $this->input('title'),
            'description' => $this->input('description'),
            'language_id' => (int) $this->input('language_id'),
            'level'       => $this->input('level'),
            // Ommaviy to‘plamni faqat muharrir yoki admin e’lon qila oladi; o‘quvchi qiymatni yuborsa ham e’tiborga olinmaydi
            'is_public'   => Policy::canPublish($user) ? ($this->input('is_public') === '1' ? 1 : 0) : $currentPublic,
        ];
        $v = (new Validator($_POST))
            ->required('title', 'Nomi')->length('title', 'Nomi', 3, 150)
            ->length('description', 'Tavsif', 0, 2000)
            ->required('language_id', 'Til')
            ->required('level', 'Daraja')->in('level', 'Daraja', Collection::LEVELS);
        $v->rule('language_id', Language::exists($data['language_id']), 'Tanlangan til mavjud emas.');
        if ($data['title'] !== '') {
            $v->rule('title', !Collection::titleTaken($ownerId, $data['title'], $exceptId), 'Sizda shu nomli to‘plam allaqachon bor.');
        }
        return [$data, $v->errors()];
    }
}
