<?php
declare(strict_types=1);

namespace App\Core;

/** CSRF token: holatni o‘zgartiruvchi har bir POST so‘rovda tekshiriladi */
final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf']) || !is_string($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function verify(mixed $token): bool
    {
        return is_string($token)
            && !empty($_SESSION['_csrf'])
            && hash_equals((string) $_SESSION['_csrf'], $token);
    }

    public static function reset(): void
    {
        unset($_SESSION['_csrf']);
    }
}
