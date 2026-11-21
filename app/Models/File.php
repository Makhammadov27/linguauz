<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Yuklangan fayllar metama’lumotlari (fayl o‘zi storage/uploads da) */
final class File extends Model
{
    public const CATEGORIES = ['image' => 'Rasm', 'pdf' => 'PDF hujjat', 'video' => 'Video'];

    public static function find(int $id): ?array
    {
        return self::row(
            'SELECT f.*, c.title AS collection_title, c.owner_id, c.is_public, c.status
             FROM files f JOIN collections c ON c.id = f.collection_id WHERE f.id = :id',
            ['id' => $id]
        );
    }

    public static function forCollection(int $collectionId): array
    {
        return self::rows('SELECT * FROM files WHERE collection_id = :c ORDER BY category, id', ['c' => $collectionId]);
    }

    public static function search(string $q, string $category, ?array $user, int $page, int $perPage = 12): array
    {
        $p = [];
        $where = [Collection::visibility($user, $p)];
        if ($q !== '') {
            $where[] = '(f.original_name LIKE :q1 OR f.description LIKE :q2)';
            $p['q1'] = $p['q2'] = like_escape($q);
        }
        if (isset(self::CATEGORIES[$category])) {
            $where[] = 'f.category = :cat';
            $p['cat'] = $category;
        }
        $w = implode(' AND ', $where);
        $from = 'FROM files f JOIN collections c ON c.id = f.collection_id';
        $total = (int) self::value("SELECT COUNT(*) $from WHERE $w", $p);
        [$page, $pages, $offset] = self::paginate($total, $page, $perPage);
        $items = self::rows(
            "SELECT f.*, c.title AS collection_title, c.owner_id $from WHERE $w ORDER BY f.created_at DESC, f.id DESC LIMIT $perPage OFFSET $offset",
            $p
        );
        return compact('items', 'total', 'page', 'pages');
    }

    public static function create(int $collectionId, int $uploaderId, array $meta, string $stored, string $description): int
    {
        return self::insert(
            'INSERT INTO files (collection_id, uploader_id, category, original_name, stored_name, mime, size_bytes, description)
             VALUES (:c, :u, :cat, :orig, :stored, :mime, :size, :d)',
            ['c' => $collectionId, 'u' => $uploaderId, 'cat' => $meta['category'], 'orig' => $meta['original'],
             'stored' => $stored, 'mime' => $meta['mime'], 'size' => $meta['size'], 'd' => $description !== '' ? $description : null]
        );
    }

    public static function replace(int $id, int $uploaderId, array $meta, string $stored, string $description): void
    {
        self::run(
            'UPDATE files SET uploader_id = :u, category = :cat, original_name = :orig, stored_name = :stored,
                    mime = :mime, size_bytes = :size, description = :d WHERE id = :id',
            ['u' => $uploaderId, 'cat' => $meta['category'], 'orig' => $meta['original'], 'stored' => $stored,
             'mime' => $meta['mime'], 'size' => $meta['size'], 'd' => $description !== '' ? $description : null, 'id' => $id]
        );
    }

    public static function updateDescription(int $id, string $description): void
    {
        self::run('UPDATE files SET description = :d WHERE id = :id', ['d' => $description !== '' ? $description : null, 'id' => $id]);
    }

    public static function delete(int $id): void
    {
        self::run('DELETE FROM files WHERE id = :id', ['id' => $id]);
    }
}
