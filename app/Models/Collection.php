<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

/** So‘z to‘plami */
final class Collection extends Model
{
    public const LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
    public const SORTS = ['created' => 'c.created_at', 'title' => 'c.title', 'level' => 'c.level', 'words' => 'words_count'];

    /**
     * Foydalanuvchi ko‘ra oladigan to‘plamlar sharti (serverda qo‘llanadi).
     * Mehmon — faqat ommaviy; o‘quvchi/muharrir — ommaviy + o‘ziniki; admin — barchasi.
     */
    public static function visibility(?array $user, array &$params, string $alias = 'c', bool $archived = false): string
    {
        $status = $archived ? "$alias.status = 'archived'" : "$alias.status = 'active'";
        if ($user !== null && $user['role'] === 'admin') {
            return $status;
        }
        if ($user !== null) {
            $params['vis_uid'] = (int) $user['id'];
            return $archived
                ? "$status AND $alias.owner_id = :vis_uid"
                : "$status AND ($alias.is_public = 1 OR $alias.owner_id = :vis_uid)";
        }
        return $archived ? '1 = 0' : "$status AND $alias.is_public = 1";
    }

    public static function search(array $f, ?array $user, int $page, int $perPage = 9): array
    {
        $p = [];
        $where = [self::visibility($user, $p, 'c', !empty($f['archived']))];
        if ($f['q'] !== '') {
            $where[] = '(c.title LIKE :q1 OR c.description LIKE :q2)';
            $p['q1'] = $p['q2'] = like_escape($f['q']);
        }
        if ($f['language_id'] > 0) {
            $where[] = 'c.language_id = :lang';
            $p['lang'] = $f['language_id'];
        }
        if (in_array($f['level'], self::LEVELS, true)) {
            $where[] = 'c.level = :level';
            $p['level'] = $f['level'];
        }
        if (!empty($f['mine']) && $user !== null) {
            $where[] = 'c.owner_id = :mine';
            $p['mine'] = (int) $user['id'];
        }
        $w = implode(' AND ', $where);
        $total = (int) self::value("SELECT COUNT(*) FROM collections c WHERE $w", $p);
        [$page, $pages, $offset] = self::paginate($total, $page, $perPage);
        $order = (self::SORTS[$f['sort']] ?? 'c.created_at') . ' ' . ($f['dir'] === 'asc' ? 'ASC' : 'DESC');

        $items = self::rows(
            "SELECT c.*, l.name AS language_name, l.native_name, l.code AS language_code, u.name AS owner_name,
                    (SELECT COUNT(*) FROM words w WHERE w.collection_id = c.id) AS words_count,
                    (SELECT f.id FROM files f WHERE f.collection_id = c.id AND f.category = 'image' ORDER BY f.id LIMIT 1) AS cover_id
             FROM collections c
             JOIN languages l ON l.id = c.language_id
             JOIN users u ON u.id = c.owner_id
             WHERE $w ORDER BY $order, c.id DESC LIMIT $perPage OFFSET $offset",
            $p
        );
        return compact('items', 'total', 'page', 'pages');
    }

    public static function find(int $id): ?array
    {
        return self::row(
            "SELECT c.*, l.name AS language_name, l.native_name, l.code AS language_code, l.speech_code,
                    u.name AS owner_name,
                    (SELECT COUNT(*) FROM words w WHERE w.collection_id = c.id) AS words_count
             FROM collections c
             JOIN languages l ON l.id = c.language_id
             JOIN users u ON u.id = c.owner_id
             WHERE c.id = :id",
            ['id' => $id]
        );
    }

    public static function latestPublic(int $limit = 6): array
    {
        $p = [];
        return self::search(['q' => '', 'language_id' => 0, 'level' => '', 'sort' => 'created', 'dir' => 'desc'], null, 1, $limit)['items'];
    }

    /** So‘z yoki mashq qo‘shish mumkin bo‘lgan to‘plamlar (forma uchun ro‘yxat) */
    public static function editableBy(array $user): array
    {
        if ($user['role'] === 'admin') {
            return self::rows("SELECT c.id, c.title, l.name AS language_name FROM collections c JOIN languages l ON l.id = c.language_id
                               WHERE c.status = 'active' ORDER BY c.title");
        }
        return self::rows(
            "SELECT c.id, c.title, l.name AS language_name FROM collections c JOIN languages l ON l.id = c.language_id
             WHERE c.status = 'active' AND c.owner_id = :uid ORDER BY c.title",
            ['uid' => (int) $user['id']]
        );
    }

    /** Filtr uchun ko‘rinadigan to‘plamlar ro‘yxati */
    public static function visibleOptions(?array $user): array
    {
        $p = [];
        $w = self::visibility($user, $p);
        return self::rows("SELECT c.id, c.title FROM collections c WHERE $w ORDER BY c.title", $p);
    }

    public static function titleTaken(int $ownerId, string $title, int $exceptId = 0): bool
    {
        return (bool) self::value(
            'SELECT 1 FROM collections WHERE owner_id = :o AND title = :t AND id <> :id',
            ['o' => $ownerId, 't' => $title, 'id' => $exceptId]
        );
    }

    public static function create(array $d, int $ownerId): int
    {
        return self::insert(
            'INSERT INTO collections (language_id, owner_id, title, description, level, is_public)
             VALUES (:lang, :owner, :title, :descr, :level, :pub)',
            ['lang' => $d['language_id'], 'owner' => $ownerId, 'title' => $d['title'], 'descr' => $d['description'] ?: null,
             'level' => $d['level'], 'pub' => $d['is_public']]
        );
    }

    public static function update(int $id, array $d): void
    {
        self::run(
            'UPDATE collections SET language_id = :lang, title = :title, description = :descr, level = :level, is_public = :pub WHERE id = :id',
            ['lang' => $d['language_id'], 'title' => $d['title'], 'descr' => $d['description'] ?: null,
             'level' => $d['level'], 'pub' => $d['is_public'], 'id' => $id]
        );
    }

    /** Boshqa foydalanuvchilar ushbu to‘plamdan o‘rganayotganmi (takrorlash rejasi yoki mashq natijasi) */
    public static function learnersCount(int $id, int $exceptUserId = 0): int
    {
        return (int) self::value(
            'SELECT COUNT(DISTINCT x.user_id) FROM (
                SELECT rs.user_id FROM review_schedules rs JOIN words w ON w.id = rs.word_id WHERE w.collection_id = :c1
                UNION
                SELECT ea.user_id FROM exercise_attempts ea JOIN exercises e ON e.id = ea.exercise_id WHERE e.collection_id = :c2
             ) x WHERE x.user_id <> :u',
            ['c1' => $id, 'c2' => $id, 'u' => $exceptUserId]
        );
    }

    public static function setStatus(int $id, string $status): void
    {
        self::run('UPDATE collections SET status = :s WHERE id = :id', ['s' => $status, 'id' => $id]);
    }

    /**
     * To‘plamni o‘chirish. So‘zlar, mashqlar va fayl yozuvlari FK (ON DELETE CASCADE) orqali o‘chadi,
     * diskdagi fayllar esa tranzaksiya muvaffaqiyatli tugagach o‘chiriladi.
     * @return string[] diskdan o‘chirilishi kerak bo‘lgan saqlash nomlari
     */
    public static function delete(int $id): array
    {
        return Database::transaction(function () use ($id): array {
            $stored = array_column(self::rows('SELECT stored_name FROM files WHERE collection_id = :id', ['id' => $id]), 'stored_name');
            self::run('DELETE FROM collections WHERE id = :id', ['id' => $id]);
            return $stored;
        });
    }

    public static function countPublic(): int
    {
        return (int) self::value("SELECT COUNT(*) FROM collections WHERE is_public = 1 AND status = 'active'");
    }
}
