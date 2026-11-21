<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Language extends Model
{
    public static function list(): array
    {
        return self::rows(
            "SELECT l.*,
                    (SELECT COUNT(*) FROM collections c WHERE c.language_id = l.id) AS collections_count,
                    (SELECT COUNT(*) FROM words w JOIN collections c ON c.id = w.collection_id
                      WHERE c.language_id = l.id AND c.is_public = 1 AND c.status = 'active') AS public_words
             FROM languages l ORDER BY l.name"
        );
    }

    public static function options(): array
    {
        return self::rows('SELECT id, name, native_name FROM languages ORDER BY name');
    }

    public static function find(int $id): ?array
    {
        return self::row('SELECT * FROM languages WHERE id = :id', ['id' => $id]);
    }

    public static function exists(int $id): bool
    {
        return (bool) self::value('SELECT 1 FROM languages WHERE id = :id', ['id' => $id]);
    }

    public static function duplicate(string $code, string $name, int $exceptId = 0): bool
    {
        return (bool) self::value(
            'SELECT 1 FROM languages WHERE (code = :c OR name = :n) AND id <> :id',
            ['c' => $code, 'n' => $name, 'id' => $exceptId]
        );
    }

    public static function create(array $d): int
    {
        return self::insert(
            'INSERT INTO languages (code, name, native_name, speech_code) VALUES (:code, :name, :native, :speech)',
            ['code' => $d['code'], 'name' => $d['name'], 'native' => $d['native_name'], 'speech' => $d['speech_code']]
        );
    }

    public static function update(int $id, array $d): void
    {
        self::run(
            'UPDATE languages SET code = :code, name = :name, native_name = :native, speech_code = :speech WHERE id = :id',
            ['code' => $d['code'], 'name' => $d['name'], 'native' => $d['native_name'], 'speech' => $d['speech_code'], 'id' => $id]
        );
    }

    public static function isUsed(int $id): bool
    {
        return (bool) self::value('SELECT 1 FROM collections WHERE language_id = :id LIMIT 1', ['id' => $id]);
    }

    public static function delete(int $id): void
    {
        self::run('DELETE FROM languages WHERE id = :id', ['id' => $id]);
    }
}
