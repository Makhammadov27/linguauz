<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Collection;
use App\Models\Exercise;
use App\Models\Review;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $user = $this->requireAuth();
        $uid = (int) $user['id'];
        $this->view('dashboard/index', [
            'title'       => 'Shaxsiy kabinet',
            'noindex'     => true,
            'user'        => $user,
            'stats'       => Review::stats($uid),
            'upcoming'    => Review::upcoming($uid, 7),
            'nextDue'     => Review::nextDueDate($uid),
            'attempts'    => Exercise::userAttempts($uid, 0, 5),
            'myCollections' => Collection::search(
                ['q' => '', 'language_id' => 0, 'level' => '', 'mine' => 1, 'sort' => 'created', 'dir' => 'desc'],
                $user,
                1,
                6
            )['items'],
        ]);
    }
}
