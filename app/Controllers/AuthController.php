<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Models\LoginAttempt;
use App\Models\User;

final class AuthController extends Controller
{
    /** Vaqt bo‘yicha farqni yo‘qotish uchun mavjud bo‘lmagan foydalanuvchida ham password_verify bajariladi */
    private const DUMMY_HASH = '$2y$10$Tv36oCd27QK4.N14Sz9bK.RvLsA92d1hQo916DI.wrU1FxSAZrDwu';

    public function loginForm(): void
    {
        if (Auth::user()) {
            $this->redirect('/dashboard');
        }
        $this->view('auth/login', ['title' => 'Kirish', 'noindex' => true, 'old' => [], 'errors' => []]);
    }

    public function login(): void
    {
        $this->verifyCsrf();
        $email = mb_strtolower($this->input('email'));
        $password = (string) ($_POST['password'] ?? '');
        $ip = client_ip();
        $data = ['title' => 'Kirish', 'noindex' => true, 'old' => ['email' => $email], 'errors' => []];

        if ($email === '' || $password === '') {
            $data['errors']['form'] = 'Elektron pochta va parolni kiriting.';
            $this->view('auth/login', $data, 422);
            return;
        }
        if (LoginAttempt::tooMany($email, $ip)) {
            $data['errors']['form'] = 'Kirish urinishlari soni oshib ketdi. ' . (int) config('security.login_window_minutes') . ' daqiqadan so‘ng qayta urinib ko‘ring.';
            $this->view('auth/login', $data, 429);
            return;
        }

        $user = User::findByEmail($email);
        $valid = password_verify($password, $user['password_hash'] ?? self::DUMMY_HASH);
        if (!$user || !$valid || !(int) $user['is_active']) {
            LoginAttempt::record($email, $ip);
            $data['errors']['form'] = 'Elektron pochta yoki parol noto‘g‘ri.'; // umumiy xabar
            $this->view('auth/login', $data, 422);
            return;
        }

        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            User::updatePassword((int) $user['id'], $password);
        }
        LoginAttempt::clear($email);
        LoginAttempt::prune();
        Auth::login($user);
        Session::flash('success', 'Xush kelibsiz, ' . $user['name'] . '!');

        $intended = Session::get('_intended');
        Session::forget('_intended');
        if (is_string($intended) && str_starts_with($intended, '/') && !str_starts_with($intended, '//')) {
            header('Location: ' . url($intended), true, 303);
            exit;
        }
        $this->redirect('/dashboard');
    }

    public function registerForm(): void
    {
        if (Auth::user()) {
            $this->redirect('/dashboard');
        }
        $this->view('auth/register', ['title' => 'Ro‘yxatdan o‘tish', 'noindex' => true, 'old' => [], 'errors' => []]);
    }

    public function register(): void
    {
        $this->verifyCsrf();
        $old = ['name' => $this->input('name'), 'email' => mb_strtolower($this->input('email'))];
        $v = (new Validator($_POST + ['email_norm' => $old['email']]))
            ->required('name', 'Ism')->length('name', 'Ism', 2, 100)
            ->required('email', 'Elektron pochta')->email('email', 'Elektron pochta')
            ->required('password', 'Parol')->length('password', 'Parol', 8, 72)
            ->regex('password', '/^(?=.*\p{L})(?=.*\d).+$/u', 'Parolda kamida bitta harf va bitta raqam bo‘lishi kerak.')
            ->matches('password_confirm', 'password', 'Parollar bir-biriga mos kelmadi.');
        $v->rule('email', !User::emailExists($old['email']), 'Bu elektron pochta bilan hisob allaqachon mavjud.');

        if ($v->fails()) {
            $this->view('auth/register', ['title' => 'Ro‘yxatdan o‘tish', 'noindex' => true, 'old' => $old, 'errors' => $v->errors()], 422);
            return;
        }
        // Ro‘yxatdan o‘tgan foydalanuvchi har doim "user" rolini oladi — rolni o‘zi tanlay olmaydi
        $id = User::create($old['name'], $old['email'], (string) $_POST['password'], 'user');
        Auth::login(['id' => $id]);
        Session::flash('success', 'Hisobingiz yaratildi. Birinchi to‘plamni tanlab, o‘rganishni boshlang!');
        $this->redirect('/collections');
    }

    public function logout(): void
    {
        $this->verifyCsrf();
        Auth::logout();
        Session::flash('info', 'Siz tizimdan chiqdingiz.');
        $this->redirect('/');
    }
}
