<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** So‘z — asosiy predmet obyekti */
final class Word extends Model
{
    public const POS = [
        'noun'      => 'Ot',
        'verb'      => 'Fe’l',
        'adjective' => 'Sifat',
        'adverb'    => 'Ravish',
        'phrase'    => 'Ibora',
        'other'     => 'Boshqa',
    ];

    /** Saralash faqat shu ustunlar bo‘yicha (SQL inyeksiyadan himoya) */
    public const SORTS = [
        'term'        => 'w.term',
        'translation' => 'w.translation',
        'collection'  => 'c.title',
        'language'    => 'l.name',
        'level'       => 'c.level',
        'created'     => 'w.created_at',
    ];

    private const FROM = 'FROM words w
        JOIN collections c ON c.id = w.collection_id
        JOIN languages l ON l.id = c.language_id';

    /** Filtrlar bo‘yicha WHERE qismini yasash (ro‘yxat va eksport uchun umumiy) */
    private static function where(array $f, ?array $user, array &$p): string
    {
        $where = [Collection::visibility($user, $p)];
        if ($f['q'] !== '') {
            $where[] = '(w.term LIKE :q1 OR w.translation LIKE :q2 OR w.example LIKE :q3)';
            $p['q1'] = $p['q2'] = $p['q3'] = like_escape($f['q']);
        }
        if ($f['language_id'] > 0) {
            $where[] = 'c.language_id = :lang';
            $p['lang'] = $f['language_id'];
        }
        if ($f['collection_id'] > 0) {
            $where[] = 'w.collection_id = :col';
            $p['col'] = $f['collection_id'];
        }
        if (in_array($f['level'], Collection::LEVELS, true)) {
            $where[] = 'c.level = :level';
            $p['level'] = $f['level'];
        }
        if (isset(self::POS[$f['pos']])) {
            $where[] = 'w.part_of_speech = :pos';
            $p['pos'] = $f['pos'];
        }
        if (!empty($f['mine']) && $user !== null) {
            $where[] = 'c.owner_id = :mine';
            $p['mine'] = (int) $user['id'];
        }
        return implode(' AND ', $where);
    }

    private static function order(array $f): string
    {
        $col = self::SORTS[$f['sort']] ?? 'w.term';
        $dir = $f['dir'] === 'desc' ? 'DESC' : 'ASC';
        return "$col $dir, w.id ASC";
    }

    public static function search(array $f, ?array $user, int $page, int $perPage = 15): array
    {
        $p = [];
        $w = self::where($f, $user, $p);
        $total = (int) self::value('SELECT COUNT(*) ' . self::FROM . " WHERE $w", $p);
        [$page, $pages, $offset] = self::paginate($total, $page, $perPage);
        $items = self::rows(
            'SELECT w.*, c.title AS collection_title, c.level, c.owner_id, c.is_public, l.name AS language_name, l.code AS language_code, l.speech_code '
            . self::FROM . " WHERE $w ORDER BY " . self::order($f) . " LIMIT $perPage OFFSET $offset",
            $p
        );
        return compact('items', 'total', 'page', 'pages');
    }

    /** Eksport: joriy filtr va saralashga mos BARCHA ruxsat etilgan yozuvlar (sahifalashsiz) */
    public static function exportRows(array $f, ?array $user): \Generator
    {
        $p = [];
        $w = self::where($f, $user, $p);
        $st = self::db()->prepare(
            'SELECT w.term, w.translation, w.transcription, w.part_of_speech, w.example, w.example_translation,
                    c.title AS collection_title, l.name AS language_name, c.level, w.created_at '
            . self::FROM . " WHERE $w ORDER BY " . self::order($f)
        );
        $st->execute($p);
        while ($r = $st->fetch()) {
            yield [
                $r['term'], $r['translation'], $r['transcription'], self::POS[$r['part_of_speech']] ?? '',
                $r['example'], $r['example_translation'], $r['collection_title'], $r['language_name'], $r['level'],
                fmt_date($r['created_at'], 'Y-m-d H:i'),
            ];
        }
    }

    public static function find(int $id): ?array
    {
        return self::row(
            'SELECT w.*, c.title AS collection_title, c.owner_id, c.is_public, c.status, c.level,
                    l.name AS language_name, l.code AS language_code, l.speech_code, u.name AS creator_name '
            . self::FROM . ' JOIN users u ON u.id = w.created_by WHERE w.id = :id',
            ['id' => $id]
        );
    }

    public static function forCollection(int $collectionId): array
    {
        return self::rows('SELECT * FROM words WHERE collection_id = :c ORDER BY term', ['c' => $collectionId]);
    }

    public static function termTaken(int $collectionId, string $term, int $exceptId = 0): bool
    {
        return (bool) self::value(
            'SELECT 1 FROM words WHERE collection_id = :c AND term = :t AND id <> :id',
            ['c' => $collectionId, 't' => $term, 'id' => $exceptId]
        );
    }

    private static function params(array $d): array
    {
        return [
            'col'   => $d['collection_id'],
            'term'  => $d['term'],
            'tr'    => $d['translation'],
            'trs'   => $d['transcription'] !== '' ? $d['transcription'] : null,
            'pos'   => $d['part_of_speech'],
            'ex'    => $d['example'] !== '' ? $d['example'] : null,
            'ext'   => $d['example_translation'] !== '' ? $d['example_translation'] : null,
        ];
    }

    public static function create(array $d, int $userId): int
    {
        return self::insert(
            'INSERT INTO words (collection_id, created_by, term, translation, transcription, part_of_speech, example, example_translation)
             VALUES (:col, :uid, :term, :tr, :trs, :pos, :ex, :ext)',
            self::params($d) + ['uid' => $userId]
        );
    }

    public static function update(int $id, array $d): void
    {
        self::run(
            'UPDATE words SET collection_id = :col, term = :term, translation = :tr, transcription = :trs,
                    part_of_speech = :pos, example = :ex, example_translation = :ext WHERE id = :id',
            self::params($d) + ['id' => $id]
        );
    }

    public static function delete(int $id): void
    {
        self::run('DELETE FROM words WHERE id = :id', ['id' => $id]);
    }

    public static function countPublic(): int
    {
        return (int) self::value("SELECT COUNT(*) FROM words w JOIN collections c ON c.id = w.collection_id WHERE c.is_public = 1 AND c.status = 'active'");
    }
}
