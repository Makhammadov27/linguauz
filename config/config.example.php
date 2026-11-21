<?php
/**
 * Namunaviy sozlamalar. Ushbu faylni config.php nomi bilan nusxalang va qiymatlarni o‘zgartiring.
 * config.php repozitoriyga va topshirish arxiviga kiritilmaydi (haqiqiy parollar saqlanadi).
 */
return [
    'app' => [
        'name'     => 'LinguaUz',
        'env'      => 'production',     // development | production
        'debug'    => false,            // true bo‘lsa xato tafsilotlari ekranga chiqadi (faqat lokal sinovda)
        'timezone' => 'Asia/Tashkent',
    ],
    'db' => [
        'host'    => '127.0.0.1',
        'port'    => 3306,
        'name'    => 'linguauz',
        'user'    => 'linguauz_user',
        'pass'    => 'O‘ZGARTIRING',
        'charset' => 'utf8mb4',
    ],
    'mail' => [
        // Lokal sinov: Mailpit (https://mailpit.axllent.org) — SMTP 127.0.0.1:1025, veb-quti http://127.0.0.1:8025
        'host'       => '127.0.0.1',
        'port'       => 1025,
        'encryption' => '',             // '' | 'tls' | 'ssl'
        'username'   => '',
        'password'   => '',
        'from_email' => 'noreply@linguauz.test',
        'from_name'  => 'LinguaUz sayti',
        'to_email'   => 'admin@linguauz.test',  // aloqa xabarlari qabul qiluvchisi (foydalanuvchi o‘zgartira olmaydi)
        'timeout'    => 5,
    ],
    'security' => [
        'login_max_attempts'     => 5,  // shu oyna ichida ruxsat etilgan noto‘g‘ri urinishlar
        'login_window_minutes'   => 15,
        'contact_max_per_window' => 3,  // bitta IP dan aloqa xabarlari soni
        'contact_window_minutes' => 10,
    ],
];
