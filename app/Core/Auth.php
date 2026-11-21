<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\User;

/** Autentifikatsiya: joriy foydalanuvchini sessiya orqali aniqlash */
final class Auth
{
    private static ?array $user = null;
    private static bool $loaded = false;

    public static function user(): ?array
    {
        if (self::$loaded) {
            return self::$user;
        }
        self::$loaded = true;
        $id = $_SESSION['user_id'] ?? null;
        if (!is_int($id)) {
            return null;
        }
        $user = User::findActive($id);
        if ($user === null) {
            // Hisob o‘chirilgan yoki bloklangan — sessiyani tozalaymiz
            unset($_SESSION['user_id']);
            return null;
        }
        return self::$user = $user;
    }

    public static function id(): ?int
    {
        $u = self::user();
        return $u ? (int) $u['id'] : null;
    }

    public static function hasRole(string ...$roles): bool
    {
        $u = self::user();
        return $u !== null && in_array($u['role'], $roles, true);
    }

    public static function login(array $user): void
    {
        Session::regenerate();             // yangi sessiya identifikatori
        Csrf::reset();
        $_SESSION['user_id'] = (int) $user['id'];
        self::$loaded = false;
        self::$user = null;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        Session::regenerate();             // eski sessiya fayli o‘chiriladi
        self::$loaded = true;
        self::$user = null;
    }
}
