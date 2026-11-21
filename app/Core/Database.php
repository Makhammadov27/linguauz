<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/** MySQL bilan PDO orqali yagona ulanish */
final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }
        $c = config('db');
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $c['host'],
            (int) $c['port'],
            $c['name'],
            $c['charset'] ?? 'utf8mb4'
        );
        self::$pdo = new PDO($dsn, (string) $c['user'], (string) $c['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        // Baza va PHP bir xil vaqt mintaqasida ishlashi uchun (CURDATE(), NOW())
        self::$pdo->exec("SET time_zone = '" . (new \DateTimeImmutable())->format('P') . "'");
        return self::$pdo;
    }

    /**
     * Bir nechta amalni tranzaksiyada bajarish. Xato bo‘lsa barcha o‘zgarishlar bekor qilinadi.
     * @template T
     * @param callable(PDO):T $callback
     * @return T
     */
    public static function transaction(callable $callback): mixed
    {
        $pdo = self::pdo();
        $pdo->beginTransaction();
        try {
            $result = $callback($pdo);
            $pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}
