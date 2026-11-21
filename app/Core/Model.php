<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/** Modellar uchun PDO yordamchilari — barcha so‘rovlar parametrli */
abstract class Model
{
    protected static function db(): PDO
    {
        return Database::pdo();
    }

    protected static function row(string $sql, array $params = []): ?array
    {
        $st = self::db()->prepare($sql);
        $st->execute($params);
        $r = $st->fetch();
        return $r === false ? null : $r;
    }

    protected static function rows(string $sql, array $params = []): array
    {
        $st = self::db()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    protected static function value(string $sql, array $params = []): mixed
    {
        $st = self::db()->prepare($sql);
        $st->execute($params);
        $v = $st->fetchColumn();
        return $v === false ? null : $v;
    }

    protected static function run(string $sql, array $params = []): int
    {
        $st = self::db()->prepare($sql);
        $st->execute($params);
        return $st->rowCount();
    }

    protected static function insert(string $sql, array $params = []): int
    {
        self::run($sql, $params);
        return (int) self::db()->lastInsertId();
    }

    /** Sahifalash hisob-kitobi */
    protected static function paginate(int $total, int $page, int $perPage): array
    {
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $pages);
        return [$page, $pages, ($page - 1) * $perPage];
    }
}
