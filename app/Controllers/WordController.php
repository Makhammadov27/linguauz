<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csv;
use App\Core\Policy;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Collection;
use App\Models\Language;
use App\Models\Review;
use App\Models\Word;

final class WordController extends Controller
{
    private function filters(): array
    {
        [$sort, $dir] = $this->sortParams(Word::SORTS, 'term', 'asc');
        return [
            'q'             => mb_substr($this->query('q'), 0, 100),
            'language_id'   => $this->queryInt('language_id'),
            'collection_id' => $this->queryInt('collection_id'),
            'level'         => $this->query('level'),
            'pos'           => $this->query('pos'),
            'mine'          => $this->query('mine') === '1' ? 1 : 0,
            'sort'          => $sort,
            'dir'           => $dir,
        ];
    }

    public function index(): void
    {
        $user = Auth::user();
        $filters = $this->filters();
        $this->view('words/index', [
            'title'       => 'So‘zlar lug‘ati',
            'description' => 'Ingliz, nemis, rus, turk va boshqa tillardagi so‘zlar, tarjima, transkripsiya va misollar bilan.',
            'filters'     => $filters,
            'result'      => Word::search($filters, $user, $this->page()),
            'languages'   => Language::options(),
            'collections' => Collection::visibleOptions($user),
            'user'        => $user,
        ]);
    }

    /** CSV eksport — joriy qidiruv/filtr/saralashga mos barcha ruxsat etilgan yozuvlar */
    public function export(): void
    {
        $user = $this->requireAuth();
        $filters = $this->filters();
        Csv::download(
            'sozlar_' . date('Y-m-d_His') . '.csv',
            ['So‘z', 'Tarjima', 'Transkripsiya', 'So‘z turkumi', 'Misol', 'Misol tarjimasi', 'To‘plam', 'Til', 'Daraja', 'Qo‘shilgan sana'],
            Word::exportRows($filters, $user)
        );
    }

    public function show(int $id): void
    {
        $user = Auth::user();
        $word = Word::find($id);
        if (!$word || !Policy::canViewCollection(['owner_id' => $word['owner_id'], 'is_public' => $word['is_public'], 'status' => $word['status']], $user)) {
            abort(404); // boshqa foydalanuvchining shaxsiy so‘zi mavjudligi ham oshkor qilinmaydi
        }
        $this->view('words/show', [
            'title'       => $word['term'] . ' — tarjimasi',
            'description' => $word['term'] . ' so‘zining o‘zbekcha tarjimasi: ' . $word['translation'] . '. Transkripsiya va misollar.',
            'word'        => $word,
            'canEdit'     => Policy::canEditCollection(['owner_id' => $word['owner_id']], $user),
            'schedule'    => $user ? Review::forWord((int) $user['id'], $id) : null,
        ]);
    }

    public function create(): void
    {
        $user = $this->requireAuth();
        $collections = Collection::editableBy($user);
        if (!$collections) {
            Session::flash('info', 'So‘z qo‘shishdan oldin o‘zingizning to‘plamingizni yarating.');
            $this->redirect('/collections/create');
        }
        $this->view('words/form', [
            'title'       => 'Yangi so‘z',
            'noindex'     => true,
            'word'        => ['collection_id' => $this->queryInt('collection_id'), 'part_of_speech' => 'noun'],
            'collections' => $collections,
            'errors'      => [],
        ]);
    }

    public function store(): void
    {
        $user = $this->requireAuth();
        $this->verifyCsrf();
        [$data, $errors] = $this->validate($user);
        if ($errors) {
            $this->view('words/form', ['title' => 'Yangi so‘z', 'noindex' => true, 'word' => $data, 'collections' => Collection::editableBy($user), 'errors' => $errors], 422);
            return;
        }
        $id = Word::create($data, (int) $user['id']);
        Session::flash('success', '«' . $data['term'] . '» so‘zi qo‘shildi.');
        $this->redirect('/words/' . $id);
    }

    public function edit(int $id): void
    {
        $user = $this->requireAuth();
        $word = $this->editable($id, $user);
        $this->view('words/form', ['title' => 'So‘zni tahrirlash', 'noindex' => true, 'word' => $word, 'collections' => Collection::editableBy($user), 'errors' => []]);
    }

    public function update(int $id): void
    {
        $user = $this->requireAuth();
        $this->verifyCsrf();
        $word = $this->editable($id, $user);
        [$data, $errors] = $this->validate($user, $id);
        if ($errors) {
            $this->view('words/form', ['title' => 'So‘zni tahrirlash', 'noindex' => true, 'word' => $data + ['id' => $id], 'collections' => Collection::editableBy($user), 'errors' => $errors], 422);
            return;
        }
        Word::update($id, $data);
        Session::flash('success', 'O‘zgarishlar saqlandi.');
        $this->redirect('/words/' . $word['id']);
    }

    public function destroy(int $id): void
    {
        $user = $this->requireAuth();
        $this->verifyCsrf();
        $word = $this->editable($id, $user);
        Word::delete($id);
        Session::flash('success', '«' . $word['term'] . '» so‘zi o‘chirildi (u bilan bog‘liq takrorlash yozuvlari ham o‘chdi).');
        $this->redirect('/collections/' . $word['collection_id']);
    }

    /** Yozuv mavjudligi va egalikni serverda tekshirish */
    private function editable(int $id, array $user): array
    {
        $word = Word::find($id);
        if (!$word || !Policy::canViewCollection(['owner_id' => $word['owner_id'], 'is_public' => $word['is_public'], 'status' => $word['status']], $user)) {
            abort(404);
        }
        if (!Policy::canEditCollection(['owner_id' => $word['owner_id']], $user)) {
            abort(403, 'Siz faqat o‘zingizga tegishli to‘plamdagi so‘zlarni o‘zgartira olasiz.');
        }
        return $word;
    }

    /** @return array{0:array,1:array} */
    private function validate(array $user, int $exceptId = 0): array
    {
        $data = [
            'collection_id'       => (int) $this->input('collection_id'),
            'term'                => $this->input('term'),
            'translation'         => $this->input('translation'),
            'transcription'       => $this->input('transcription'),
            'part_of_speech'      => $this->input('part_of_speech'),
            'example'             => $this->input('example'),
            'example_translation' => $this->input('example_translation'),
        ];
        $v = (new Validator($_POST))
            ->required('collection_id', 'To‘plam')
            ->required('term', 'So‘z')->length('term', 'So‘z', 1, 120)
            ->required('translation', 'Tarjima')->length('translation', 'Tarjima', 1, 200)
            ->length('transcription', 'Transkripsiya', 1, 120)
            ->required('part_of_speech', 'So‘z turkumi')->in('part_of_speech', 'So‘z turkumi', array_keys(Word::POS))
            ->length('example', 'Misol', 2, 500)
            ->length('example_translation', 'Misol tarjimasi', 2, 500)
            ->rule('example_translation', $data['example_translation'] === '' || $data['example'] !== '', 'Misol tarjimasini kiritish uchun avval misolni yozing.');

        // Bog‘langan yozuv (to‘plam) mavjudligi va unga yozish huquqi
        $collection = $data['collection_id'] > 0 ? Collection::find($data['collection_id']) : null;
        $v->rule('collection_id', $collection !== null && $collection['status'] === 'active' && Policy::canEditCollection($collection, $user), 'Tanlangan to‘plam mavjud emas yoki unga so‘z qo‘shish huquqingiz yo‘q.');
        if ($collection && $data['term'] !== '') {
            $v->rule('term', !Word::termTaken($data['collection_id'], $data['term'], $exceptId), 'Bu so‘z tanlangan to‘plamda allaqachon mavjud.');
        }
        return [$data, $v->errors()];
    }
}
