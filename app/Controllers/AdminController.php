<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Collection;
use App\Models\ContactMessage;
use App\Models\Language;
use App\Models\User;
use App\Models\Word;

/** Boshqaruv paneli — har bir amalda administrator roli serverda tekshiriladi */
final class AdminController extends Controller
{
    public function index(): void
    {
        $this->requireRole('admin');
        $this->view('admin/index', [
            'title'   => 'Boshqaruv paneli',
            'noindex' => true,
            'stats'   => [
                'users'       => User::countAll(),
                'collections' => Collection::countPublic(),
                'words'       => Word::countPublic(),
                'unread'      => ContactMessage::unreadCount(),
            ],
        ]);
    }

    // ---------------- Foydalanuvchilar va rollar ----------------

    public function users(): void
    {
        $this->requireRole('admin');
        $q = mb_substr($this->query('q'), 0, 100);
        $role = $this->query('role');
        $this->view('admin/users', [
            'title' => 'Foydalanuvchilar', 'noindex' => true, 'q' => $q, 'role' => $role,
            'result' => User::search($q, $role, $this->page()),
        ]);
    }

    public function createUser(): void
    {
        $this->requireRole('admin');
        $this->view('admin/user_form', ['title' => 'Yangi foydalanuvchi', 'noindex' => true, 'u' => ['role' => 'user'], 'errors' => []]);
    }

    public function storeUser(): void
    {
        $this->requireRole('admin');
        $this->verifyCsrf();
        [$data, $errors] = $this->validateUser(0, true);
        if ($errors) {
            $this->view('admin/user_form', ['title' => 'Yangi foydalanuvchi', 'noindex' => true, 'u' => $data, 'errors' => $errors], 422);
            return;
        }
        User::create($data['name'], $data['email'], (string) $_POST['password'], $data['role']);
        Session::flash('success', 'Foydalanuvchi yaratildi: ' . $data['email']);
        $this->redirect('/admin/users');
    }

    public function editUser(int $id): void
    {
        $this->requireRole('admin');
        $u = User::find($id) ?? abort(404);
        $this->view('admin/user_form', ['title' => 'Foydalanuvchini tahrirlash', 'noindex' => true, 'u' => $u, 'errors' => []]);
    }

    public function updateUser(int $id): void
    {
        $admin = $this->requireRole('admin');
        $this->verifyCsrf();
        $u = User::find($id) ?? abort(404);
        [$data, $errors] = $this->validateUser($id, false);
        // Administrator o‘zini admin rolidan tushira olmaydi (tizim boshqaruvsiz qolmasligi uchun)
        if ($id === (int) $admin['id'] && $data['role'] !== 'admin') {
            $errors['role'] = 'O‘zingizning administrator rolingizni o‘zgartira olmaysiz.';
        }
        if ($errors) {
            $this->view('admin/user_form', ['title' => 'Foydalanuvchini tahrirlash', 'noindex' => true, 'u' => $data + ['id' => $id], 'errors' => $errors], 422);
            return;
        }
        User::update($id, $data['name'], $data['email'], $data['role'], (string) ($_POST['password'] ?? ''));
        Session::flash('success', 'Foydalanuvchi ma’lumotlari yangilandi: ' . $u['email']);
        $this->redirect('/admin/users');
    }

    /** Foydalanuvchini bloklash yoki faollashtirish (o‘chirish o‘rniga — tarixi saqlanadi) */
    public function toggleUser(int $id): void
    {
        $admin = $this->requireRole('admin');
        $this->verifyCsrf();
        $u = User::find($id) ?? abort(404);
        if ($id === (int) $admin['id']) {
            Session::flash('error', 'O‘z hisobingizni bloklay olmaysiz.');
            $this->redirect('/admin/users');
        }
        $active = !(int) $u['is_active'];
        User::setActive($id, $active);
        Session::flash('success', $u['email'] . ($active ? ' faollashtirildi.' : ' bloklandi — u endi tizimga kira olmaydi.'));
        $this->redirect('/admin/users');
    }

    private function validateUser(int $id, bool $passwordRequired): array
    {
        $data = ['name' => $this->input('name'), 'email' => mb_strtolower($this->input('email')), 'role' => $this->input('role')];
        $v = (new Validator($_POST))
            ->required('name', 'Ism')->length('name', 'Ism', 2, 100)
            ->required('email', 'Elektron pochta')->email('email', 'Elektron pochta')
            ->required('role', 'Rol')->in('role', 'Rol', array_keys(User::ROLES));
        if ($passwordRequired) {
            $v->required('password', 'Parol');
        }
        $v->length('password', 'Parol', 8, 72)
            ->regex('password', '/^(?=.*\p{L})(?=.*\d).+$/u', 'Parolda kamida bitta harf va bitta raqam bo‘lishi kerak.');
        $v->rule('email', !User::emailExists($data['email'], $id), 'Bu elektron pochta boshqa hisobda ishlatilgan.');
        return [$data, $v->errors()];
    }

    // ---------------- Tillar (ma’lumotnoma) ----------------

    public function languages(): void
    {
        $this->requireRole('admin');
        $this->view('admin/languages', ['title' => 'Tillar', 'noindex' => true, 'languages' => Language::list(), 'errors' => [], 'old' => []]);
    }

    public function storeLanguage(): void
    {
        $this->requireRole('admin');
        $this->verifyCsrf();
        [$data, $errors] = $this->validateLanguage(0);
        if ($errors) {
            $this->view('admin/languages', ['title' => 'Tillar', 'noindex' => true, 'languages' => Language::list(), 'errors' => $errors, 'old' => $data], 422);
            return;
        }
        Language::create($data);
        Session::flash('success', 'Til qo‘shildi: ' . $data['name']);
        $this->redirect('/admin/languages');
    }

    public function updateLanguage(int $id): void
    {
        $this->requireRole('admin');
        $this->verifyCsrf();
        Language::find($id) ?? abort(404);
        [$data, $errors] = $this->validateLanguage($id);
        if ($errors) {
            Session::flash('error', implode(' ', $errors));
            $this->redirect('/admin/languages');
        }
        Language::update($id, $data);
        Session::flash('success', 'Til ma’lumotlari yangilandi.');
        $this->redirect('/admin/languages');
    }

    public function deleteLanguage(int $id): void
    {
        $this->requireRole('admin');
        $this->verifyCsrf();
        $lang = Language::find($id) ?? abort(404);
        if (Language::isUsed($id)) {
            Session::flash('error', '«' . $lang['name'] . '» tilida to‘plamlar mavjud, shuning uchun uni o‘chirib bo‘lmaydi.');
        } else {
            Language::delete($id);
            Session::flash('success', 'Til o‘chirildi.');
        }
        $this->redirect('/admin/languages');
    }

    private function validateLanguage(int $id): array
    {
        $data = [
            'code'        => strtolower($this->input('code')),
            'name'        => $this->input('name'),
            'native_name' => $this->input('native_name'),
            'speech_code' => $this->input('speech_code'),
        ];
        $v = (new Validator($_POST))
            ->required('code', 'Kod')->regex('code', '/^[a-zA-Z]{2,3}$/', 'Kod 2–3 lotin harfidan iborat bo‘lishi kerak (masalan: en).')
            ->required('name', 'Nomi')->length('name', 'Nomi', 2, 60)
            ->required('native_name', 'O‘z tilidagi nomi')->length('native_name', 'O‘z tilidagi nomi', 2, 60)
            ->required('speech_code', 'Talaffuz kodi')->regex('speech_code', '/^[a-z]{2,3}-[A-Z]{2}$/', 'Talaffuz kodi ko‘rinishi: en-US.');
        $v->rule('code', !Language::duplicate($data['code'], $data['name'], $id), 'Bunday kod yoki nomli til allaqachon mavjud.');
        return [$data, $v->errors()];
    }

    // ---------------- Aloqa xabarlari ----------------

    public function messages(): void
    {
        $this->requireRole('admin');
        $status = $this->query('status');
        $result = ContactMessage::search($status, $this->page());
        foreach ($result['items'] as $m) {
            if (!(int) $m['is_read']) {
                ContactMessage::markRead((int) $m['id']);
            }
        }
        $this->view('admin/messages', ['title' => 'Aloqa xabarlari', 'noindex' => true, 'status' => $status, 'result' => $result]);
    }

    public function resendMessage(int $id): void
    {
        $this->requireRole('admin');
        $this->verifyCsrf();
        $m = ContactMessage::find($id) ?? abort(404);
        $ok = ContactController::deliver($id, $m['name'], $m['email'], $m['message']);
        Session::flash($ok ? 'success' : 'error', $ok ? 'Xabar pochtaga qayta yuborildi.' : 'Pochtaga yuborib bo‘lmadi — SMTP sozlamalarini tekshiring. Xabar bazada saqlanib qoldi.');
        $this->redirect('/admin/messages');
    }

    public function deleteMessage(int $id): void
    {
        $this->requireRole('admin');
        $this->verifyCsrf();
        ContactMessage::find($id) ?? abort(404);
        ContactMessage::delete($id);
        Session::flash('success', 'Xabar o‘chirildi.');
        $this->redirect('/admin/messages');
    }
}
