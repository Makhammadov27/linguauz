<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csv;
use App\Core\Session;
use App\Models\Review;

/** Takrorlash rejasi va takrorlash sessiyasi (mavzuga xos asosiy jarayon) */
final class ReviewController extends Controller
{
    private function filters(): array
    {
        [$sort, $dir] = $this->sortParams(Review::SORTS, 'due', 'asc');
        $when = $this->query('when');
        return [
            'q'             => mb_substr($this->query('q'), 0, 100),
            'collection_id' => $this->queryInt('collection_id'),
            'state'         => $this->query('state'),
            'when'          => in_array($when, ['due', 'future'], true) ? $when : '',
            'sort'          => $sort,
            'dir'           => $dir,
        ];
    }

    public function plan(): void
    {
        $user = $this->requireAuth();
        $uid = (int) $user['id'];
        $filters = $this->filters();
        $this->view('review/plan', [
            'title'       => 'Takrorlash rejasi',
            'noindex'     => true,
            'filters'     => $filters,
            'result'      => Review::plan($uid, $filters, $this->page()),
            'stats'       => Review::stats($uid),
            'upcoming'    => Review::upcoming($uid, 7),
            'collections' => Review::planCollections($uid),
        ]);
    }

    /** Faqat o‘z rejasini eksport qiladi */
    public function export(): void
    {
        $user = $this->requireAuth();
        Csv::download(
            'takrorlash_rejasi_' . date('Y-m-d') . '.csv',
            ['So‘z', 'Tarjima', 'To‘plam', 'Til', 'Holat', 'Takrorlar', 'Interval (kun)', 'Osonlik koeff.', 'Keyingi sana', 'Oxirgi takrorlash'],
            Review::planExport((int) $user['id'], $this->filters())
        );
    }

    public function session(): void
    {
        $user = $this->requireAuth();
        $uid = (int) $user['id'];
        $collectionId = $this->queryInt('collection_id');
        $card = Review::nextDue($uid, $collectionId);
        $this->view('review/session', [
            'title'        => 'Takrorlash',
            'noindex'      => true,
            'card'         => $card,
            'due'          => Review::dueCount($uid, $collectionId),
            'collectionId' => $collectionId,
            'nextDue'      => $card ? null : Review::nextDueDate($uid),
            'stats'        => Review::stats($uid),
            'collections'  => Review::planCollections($uid),
        ]);
    }

    public function answer(): void
    {
        $user = $this->requireAuth();
        $this->verifyCsrf();
        $scheduleId = (int) $this->input('schedule_id');
        $version = (int) $this->input('version');
        $quality = (int) $this->input('quality');
        $collectionId = (int) $this->input('collection_id');
        try {
            $res = Review::answer((int) $user['id'], $scheduleId, $version, $quality);
            $msg = $quality < 3
                ? 'So‘z ertaga qayta takrorlanadi.'
                : 'Keyingi takrorlash: ' . fmt_date($res['due_date']) . ' (' . plural_days($res['interval']) . ' keyin).';
            if ($res['state'] === 'mastered') {
                $msg .= ' So‘z o‘zlashtirildi deb belgilandi!';
            }
            Session::flash('success', $msg);
        } catch (\DomainException $e) {
            Session::flash('error', $e->getMessage());
        }
        $this->redirect('/review/session', $collectionId > 0 ? ['collection_id' => $collectionId] : []);
    }

    public function remove(int $id): void
    {
        $user = $this->requireAuth();
        $this->verifyCsrf();
        if (!Review::remove((int) $user['id'], $id)) {
            abort(404); // boshqa foydalanuvchining yozuvi bo‘lsa ham shu javob
        }
        Session::flash('success', 'So‘z takrorlash rejasidan olib tashlandi.');
        $this->redirect('/review');
    }
}
