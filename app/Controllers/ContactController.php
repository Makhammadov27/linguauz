<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\ErrorHandler;
use App\Core\Mailer;
use App\Core\Session;
use App\Core\Validator;
use App\Models\ContactMessage;

final class ContactController extends Controller
{
    public function show(): void
    {
        $user = Auth::user();
        $this->view('contact/index', [
            'title'       => 'Aloqa',
            'description' => 'LinguaUz jamoasiga savol, taklif yoki xatolik haqida xabar yuboring.',
            'old'         => ['name' => $user['name'] ?? '', 'email' => $user['email'] ?? '', 'message' => ''],
            'errors'      => [],
        ]);
    }

    public function send(): void
    {
        $this->verifyCsrf();
        $old = ['name' => $this->input('name'), 'email' => $this->input('email'), 'message' => $this->input('message')];
        $v = (new Validator($_POST))
            ->required('name', 'Ism')->length('name', 'Ism', 2, 100)
            ->required('email', 'Elektron pochta')->email('email', 'Elektron pochta')
            ->required('message', 'Xabar')->length('message', 'Xabar', 10, 3000);
        // Botlarga qarshi yashirin maydon: odam uni to‘ldirmaydi
        $v->rule('website', $this->input('website') === '', 'So‘rov rad etildi.');

        $ip = client_ip();
        $limit = (int) config('security.contact_max_per_window', 3);
        $window = (int) config('security.contact_window_minutes', 10);
        if (ContactMessage::recentCountByIp($ip, $window) >= $limit) {
            $v->rule('form', false, "Juda ko‘p xabar yuborildi. {$window} daqiqadan so‘ng qayta urinib ko‘ring.");
        }
        if ($v->fails()) {
            $this->view('contact/index', ['title' => 'Aloqa', 'old' => $old, 'errors' => $v->errors()], isset($v->errors()['form']) ? 429 : 422);
            return;
        }

        // 1) Avval bazaga saqlanadi — pochta ishlamasa ham xabar yo‘qolmaydi
        $id = ContactMessage::create($old['name'], $old['email'], $old['message'], $ip);
        // 2) Keyin administrator manziliga (server sozlamasidagi) SMTP orqali yuboriladi
        $this->deliver($id, $old['name'], $old['email'], $old['message']);

        Session::flash('success', 'Rahmat! Xabaringiz qabul qilindi, tez orada javob beramiz.');
        $this->redirect('/contact');
    }

    /** Pochta orqali yuborish va natijani qayd etish (AdminController ham qayta yuborishda ishlatadi) */
    public static function deliver(int $id, string $name, string $email, string $message): bool
    {
        $body = "LinguaUz saytidan yangi aloqa xabari (#{$id})\n\n"
            . "Ism: {$name}\nPochta: {$email}\nSana: " . date('d.m.Y H:i') . "\n\n"
            . "Xabar:\n{$message}\n";
        try {
            Mailer::send((string) config('mail.to_email'), 'LinguaUz: yangi aloqa xabari #' . $id, $body, $email);
            ContactMessage::setMailStatus($id, 'sent');
            ErrorHandler::info("Aloqa xabari #{$id} pochtaga yuborildi.");
            return true;
        } catch (\Throwable $e) {
            ContactMessage::setMailStatus($id, 'failed', $e->getMessage());
            ErrorHandler::info("Aloqa xabari #{$id} pochtaga yuborilmadi: " . $e->getMessage());
            return false;
        }
    }
}
