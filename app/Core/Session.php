<?php
declare(strict_types=1);

namespace App\Core;

/** Sessiya va bir martalik (flash) xabarlar */
final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (string) ($_SERVER['SERVER_PORT'] ?? '') === '443';

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        session_name('linguauz_sid');
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => base_path_url() === '' ? '/' : base_path_url() . '/',
            'secure'   => $https,          // HTTPS muhitida Secure belgisi
            'httponly' => true,            // JavaScript cookie ni o‘qiy olmaydi
            'samesite' => 'Lax',           // Boshqa saytdan POST bilan cookie yuborilmaydi
        ]);
        session_start();
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function flash(string $type, string $message): void
    {
        $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
    }

    /** @return array<int, array{type:string,message:string}> */
    public static function pullFlashes(): array
    {
        $items = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $items;
    }

    /** Tizimga kirishda sessiya identifikatorini yangilash (session fixation himoyasi) */
    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }
}
