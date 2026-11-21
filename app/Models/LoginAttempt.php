<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Kirish urinishlarini cheklash (parolni tanlab topishdan himoya) */
final class LoginAttempt extends Model
{
    public static function tooMany(string $email, string $ip): bool
    {
        $window = (int) config('security.login_window_minutes', 15);
        $max = (int) config('security.login_max_attempts', 5);
        $count = (int) self::value(
            "SELECT COUNT(*) FROM login_attempts
             WHERE (ip = :ip OR email = :email) AND attempted_at > NOW() - INTERVAL $window MINUTE",
            ['ip' => $ip, 'email' => $email]
        );
        return $count >= $max;
    }

    public static function record(string $email, string $ip): void
    {
        self::run('INSERT INTO login_attempts (email, ip) VALUES (:e, :ip)', ['e' => mb_substr($email, 0, 150), 'ip' => $ip]);
    }

    /**
     * Muvaffaqiyatli kirishdan so‘ng faqat shu hisob bo‘yicha urinishlar tozalanadi.
     * IP bo‘yicha yozuvlar saqlanadi — aks holda hujumchi o‘z hisobiga kirib, hisoblagichni nolga tushirishi mumkin edi.
     */
    public static function clear(string $email): void
    {
        self::run('DELETE FROM login_attempts WHERE email = :e', ['e' => $email]);
    }

    /** Eski yozuvlarni tozalash (jadval cheksiz o‘smasligi uchun) */
    public static function prune(): void
    {
        self::run('DELETE FROM login_attempts WHERE attempted_at < NOW() - INTERVAL 1 DAY');
    }
}
