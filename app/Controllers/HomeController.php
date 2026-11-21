<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Collection;
use App\Models\Language;
use App\Models\User;
use App\Models\Word;

final class HomeController extends Controller
{
    public function index(): void
    {
        $this->view('home/index', [
            'description' => 'LinguaUz — ingliz, nemis, rus, turk va boshqa tillardagi so‘zlarni to‘plamlar, mashqlar va aqlli takrorlash rejasi orqali o‘rganish platformasi.',
            'languages'   => Language::list(),
            'collections' => Collection::latestPublic(6),
            'stats'       => [
                'languages'   => count(Language::options()),
                'collections' => Collection::countPublic(),
                'words'       => Word::countPublic(),
                'users'       => User::countAll(),
            ],
        ]);
    }

    public function about(): void
    {
        $this->view('home/about', [
            'title'       => 'Biz haqimizda',
            'description' => 'LinguaUz loyihasi haqida: maqsad, intervalli takrorlash usuli va platforma imkoniyatlari.',
        ]);
    }
}
