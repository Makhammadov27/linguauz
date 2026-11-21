<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;
use DomainException;
use PDO;

/**
 * Takrorlash rejasi — mavzuga xos asosiy jarayon.
 * SM-2 algoritmi: javob sifatiga (1–5) ko‘ra so‘zning keyingi takrorlash sanasi hisoblanadi.
 *
 * Holatlar: new (yangi) → learning (o‘rganilmoqda) → review (takrorlanmoqda) → mastered (o‘zlashtirilgan, interval ≥ 21 kun).
 * Xato javob (sifat < 3) har qanday holatdan learning ga qaytaradi.
 */
final class Review extends Model
{
    public const QUALITIES = [1 => 'Qayta', 3 => 'Qiyin', 4 => 'Yaxshi', 5 => 'Oson'];

    public const STATES = [
        'new'      => 'Yangi',
        'learning' => 'O‘rganilmoqda',
        'review'   => 'Takrorlanmoqda',
        'mastered' => 'O‘zlashtirilgan',
    ];

    public const SORTS = ['due' => 'rs.due_date', 'term' => 'w.term', 'interval' => 'rs.interval_days', 'ease' => 'rs.ease_factor'];

    public const MASTERED_INTERVAL = 21;

    /** SM-2 hisob-kitobi (JavaScript da ham xuddi shu formula oldindan ko‘rsatish uchun ishlatiladi) */
    public static function compute(int $repetitions, int $interval, float $ease, int $quality): array
    {
        if ($quality < 3) {
            $repetitions = 0;
            $interval = 1;
        } else {
            $repetitions++;
            $interval = match ($repetitions) {
                1 => 1,
                2 => 6,
                default => max(1, (int) round($interval * $ease)),
            };
        }
        $ease = $ease + (0.1 - (5 - $quality) * (0.08 + (5 - $quality) * 0.02));
        $ease = round(max(1.3, min(5.0, $ease)), 2);
        $interval = min($interval, 365);

        $state = match (true) {
            $quality < 3 => 'learning',
            $interval >= self::MASTERED_INTERVAL => 'mastered',
            $repetitions >= 2 => 'review',
            default => 'learning',
        };
        return ['repetitions' => $repetitions, 'interval' => $interval, 'ease' => $ease, 'state' => $state];
    }

    /** To‘plamdagi barcha so‘zlarni foydalanuvchi rejasiga qo‘shish. Takroriy so‘zlar UNIQUE kalit tufayli qo‘shilmaydi. */
    public static function startCollection(int $userId, int $collectionId): int
    {
        return Database::transaction(function (PDO $pdo) use ($userId, $collectionId): int {
            $st = $pdo->prepare(
                "INSERT IGNORE INTO review_schedules (user_id, word_id, due_date, state)
                 SELECT :uid, w.id, CURDATE(), 'new' FROM words w WHERE w.collection_id = :cid"
            );
            $st->execute(['uid' => $userId, 'cid' => $collectionId]);
            return $st->rowCount();
        });
    }

    public static function inPlanCount(int $userId, int $collectionId): int
    {
        return (int) self::value(
            'SELECT COUNT(*) FROM review_schedules rs JOIN words w ON w.id = rs.word_id WHERE rs.user_id = :u AND w.collection_id = :c',
            ['u' => $userId, 'c' => $collectionId]
        );
    }

    /** Bugun takrorlanishi kerak bo‘lgan navbatdagi so‘z */
    public static function nextDue(int $userId, int $collectionId = 0): ?array
    {
        $p = ['uid' => $userId, 'today' => date('Y-m-d')];
        $extra = '';
        if ($collectionId > 0) {
            $extra = ' AND w.collection_id = :cid';
            $p['cid'] = $collectionId;
        }
        return self::row(
            "SELECT rs.*, w.term, w.translation, w.transcription, w.part_of_speech, w.example, w.example_translation,
                    c.id AS collection_id, c.title AS collection_title, l.name AS language_name, l.speech_code
             FROM review_schedules rs
             JOIN words w ON w.id = rs.word_id
             JOIN collections c ON c.id = w.collection_id
             JOIN languages l ON l.id = c.language_id
             WHERE rs.user_id = :uid AND rs.due_date <= :today $extra
             ORDER BY rs.due_date ASC, (rs.state = 'new') ASC, rs.id ASC
             LIMIT 1",
            $p
        );
    }

    public static function dueCount(int $userId, int $collectionId = 0): int
    {
        $p = ['uid' => $userId, 'today' => date('Y-m-d')];
        $extra = '';
        if ($collectionId > 0) {
            $extra = ' AND w.collection_id = :cid';
            $p['cid'] = $collectionId;
        }
        return (int) self::value(
            "SELECT COUNT(*) FROM review_schedules rs JOIN words w ON w.id = rs.word_id
             WHERE rs.user_id = :uid AND rs.due_date <= :today $extra",
            $p
        );
    }

    public static function nextDueDate(int $userId): ?string
    {
        $v = self::value('SELECT MIN(due_date) FROM review_schedules WHERE user_id = :u AND due_date > :t', ['u' => $userId, 't' => date('Y-m-d')]);
        return $v ? (string) $v : null;
    }

    public static function stats(int $userId): array
    {
        $r = self::row(
            "SELECT COUNT(*) AS total,
                    COALESCE(SUM(due_date <= :t), 0) AS due,
                    COALESCE(SUM(state = 'new'), 0) AS new_count,
                    COALESCE(SUM(state = 'learning'), 0) AS learning,
                    COALESCE(SUM(state = 'review'), 0) AS review,
                    COALESCE(SUM(state = 'mastered'), 0) AS mastered
             FROM review_schedules WHERE user_id = :u",
            ['u' => $userId, 't' => date('Y-m-d')]
        ) ?? [];
        $r['reviewed_today'] = (int) self::value(
            'SELECT COUNT(*) FROM review_logs WHERE user_id = :u AND reviewed_at >= :d',
            ['u' => $userId, 'd' => date('Y-m-d 00:00:00')]
        );
        $r['progress'] = $r['total'] > 0 ? (int) round($r['mastered'] * 100 / $r['total']) : 0;
        return array_map('intval', $r);
    }

    /** Keyingi 7 kun uchun takrorlanadigan so‘zlar soni (bugungi kunga muddati o‘tganlar ham qo‘shiladi) */
    public static function upcoming(int $userId, int $days = 7): array
    {
        $today = new \DateTimeImmutable('today');
        $end = $today->modify('+' . ($days - 1) . ' day')->format('Y-m-d');
        $rows = self::rows(
            'SELECT GREATEST(due_date, :t1) AS d, COUNT(*) AS n FROM review_schedules
             WHERE user_id = :u AND due_date <= :end GROUP BY d',
            ['u' => $userId, 't1' => $today->format('Y-m-d'), 'end' => $end]
        );
        $map = array_column($rows, 'n', 'd');
        $out = [];
        for ($i = 0; $i < $days; $i++) {
            $d = $today->modify("+$i day")->format('Y-m-d');
            $out[$d] = (int) ($map[$d] ?? 0);
        }
        return $out;
    }

    private static function planWhere(int $userId, array $f, array &$p): string
    {
        $p['uid'] = $userId;
        $w = ['rs.user_id = :uid'];
        if ($f['collection_id'] > 0) {
            $w[] = 'w.collection_id = :cid';
            $p['cid'] = $f['collection_id'];
        }
        if (isset(self::STATES[$f['state']])) {
            $w[] = 'rs.state = :state';
            $p['state'] = $f['state'];
        }
        if ($f['when'] === 'due') {
            $w[] = 'rs.due_date <= :today';
            $p['today'] = date('Y-m-d');
        } elseif ($f['when'] === 'future') {
            $w[] = 'rs.due_date > :today';
            $p['today'] = date('Y-m-d');
        }
        if ($f['q'] !== '') {
            $w[] = '(w.term LIKE :q1 OR w.translation LIKE :q2)';
            $p['q1'] = $p['q2'] = like_escape($f['q']);
        }
        return implode(' AND ', $w);
    }

    public static function plan(int $userId, array $f, int $page, int $perPage = 15): array
    {
        $p = [];
        $w = self::planWhere($userId, $f, $p);
        $from = 'FROM review_schedules rs JOIN words w ON w.id = rs.word_id JOIN collections c ON c.id = w.collection_id JOIN languages l ON l.id = c.language_id';
        $total = (int) self::value("SELECT COUNT(*) $from WHERE $w", $p);
        [$page, $pages, $offset] = self::paginate($total, $page, $perPage);
        $order = (self::SORTS[$f['sort']] ?? 'rs.due_date') . ($f['dir'] === 'desc' ? ' DESC' : ' ASC') . ', rs.id';
        $items = self::rows(
            "SELECT rs.*, w.term, w.translation, c.id AS collection_id, c.title AS collection_title, l.name AS language_name
             $from WHERE $w ORDER BY $order LIMIT $perPage OFFSET $offset",
            $p
        );
        return compact('items', 'total', 'page', 'pages');
    }

    public static function planExport(int $userId, array $f): \Generator
    {
        $p = [];
        $w = self::planWhere($userId, $f, $p);
        $order = (self::SORTS[$f['sort']] ?? 'rs.due_date') . ($f['dir'] === 'desc' ? ' DESC' : ' ASC');
        $st = self::db()->prepare(
            "SELECT w.term, w.translation, c.title AS collection_title, l.name AS language_name, rs.state,
                    rs.repetitions, rs.interval_days, rs.ease_factor, rs.due_date, rs.last_reviewed_at
             FROM review_schedules rs JOIN words w ON w.id = rs.word_id JOIN collections c ON c.id = w.collection_id
             JOIN languages l ON l.id = c.language_id WHERE $w ORDER BY $order"
        );
        $st->execute($p);
        while ($r = $st->fetch()) {
            yield [
                $r['term'], $r['translation'], $r['collection_title'], $r['language_name'], self::STATES[$r['state']],
                $r['repetitions'], $r['interval_days'], $r['ease_factor'], $r['due_date'],
                $r['last_reviewed_at'] ? fmt_date($r['last_reviewed_at'], 'Y-m-d H:i') : '',
            ];
        }
    }

    public static function planCollections(int $userId): array
    {
        return self::rows(
            'SELECT DISTINCT c.id, c.title FROM review_schedules rs JOIN words w ON w.id = rs.word_id
             JOIN collections c ON c.id = w.collection_id WHERE rs.user_id = :u ORDER BY c.title',
            ['u' => $userId]
        );
    }

    public static function forWord(int $userId, int $wordId): ?array
    {
        return self::row('SELECT * FROM review_schedules WHERE user_id = :u AND word_id = :w', ['u' => $userId, 'w' => $wordId]);
    }

    /**
     * Javobni qabul qilish. Tranzaksiya ichida yozuv qulflanadi (FOR UPDATE), versiya tekshiriladi:
     *  - boshqa foydalanuvchining yozuvi → topilmadi;
     *  - sanasi kelmagan so‘z → rad etiladi;
     *  - takroriy (ikkinchi marta yuborilgan) javob → versiya mos kelmaydi va rad etiladi.
     */
    public static function answer(int $userId, int $scheduleId, int $version, int $quality): array
    {
        if (!isset(self::QUALITIES[$quality])) {
            throw new DomainException('Baho qiymati noto‘g‘ri.');
        }
        $today = date('Y-m-d');

        return Database::transaction(function (PDO $pdo) use ($userId, $scheduleId, $version, $quality, $today): array {
            $st = $pdo->prepare('SELECT * FROM review_schedules WHERE id = :id AND user_id = :uid FOR UPDATE');
            $st->execute(['id' => $scheduleId, 'uid' => $userId]);
            $s = $st->fetch();
            if (!$s) {
                throw new DomainException('Takrorlash yozuvi topilmadi.');
            }
            if ((int) $s['version'] !== $version) {
                throw new DomainException('Bu so‘zga javob allaqachon qabul qilingan — takroriy so‘rov rad etildi.');
            }
            if ($s['due_date'] > $today) {
                throw new DomainException('Bu so‘zning takrorlash sanasi hali kelmagan (' . fmt_date($s['due_date']) . ').');
            }

            $calc = self::compute((int) $s['repetitions'], (int) $s['interval_days'], (float) $s['ease_factor'], $quality);
            $newDue = (new \DateTimeImmutable($today))->modify('+' . $calc['interval'] . ' day')->format('Y-m-d');

            $up = $pdo->prepare(
                'UPDATE review_schedules
                 SET repetitions = :r, interval_days = :i, ease_factor = :e, due_date = :d, state = :s,
                     last_reviewed_at = NOW(), version = version + 1
                 WHERE id = :id AND version = :v'
            );
            $up->execute([
                'r' => $calc['repetitions'], 'i' => $calc['interval'], 'e' => $calc['ease'], 'd' => $newDue,
                's' => $calc['state'], 'id' => $scheduleId, 'v' => $version,
            ]);
            if ($up->rowCount() !== 1) {
                throw new DomainException('Javob allaqachon qabul qilingan.');
            }

            $log = $pdo->prepare(
                'INSERT INTO review_logs (schedule_id, user_id, word_id, quality, prev_interval, new_interval, prev_due, new_due)
                 VALUES (:sid, :uid, :wid, :q, :pi, :ni, :pd, :nd)'
            );
            $log->execute([
                'sid' => $scheduleId, 'uid' => $userId, 'wid' => $s['word_id'], 'q' => $quality,
                'pi' => $s['interval_days'], 'ni' => $calc['interval'], 'pd' => $s['due_date'], 'nd' => $newDue,
            ]);

            return ['due_date' => $newDue, 'interval' => $calc['interval'], 'state' => $calc['state']];
        });
    }

    /** Mashqda xato javob berilgan so‘zlarni bugungi takrorlashga olib kelish (faqat rejadagi so‘zlar) */
    public static function bringForward(PDO $pdo, int $userId, array $wordIds): int
    {
        if (!$wordIds) {
            return 0;
        }
        $marks = [];
        $p = ['uid' => $userId, 'today' => date('Y-m-d')];
        foreach (array_values($wordIds) as $i => $id) {
            $marks[] = ':w' . $i;
            $p['w' . $i] = (int) $id;
        }
        $st = $pdo->prepare(
            // Xato javob — SM-2 dagi "eslanmadi" holati: takrorlar va interval nolga tushiriladi
            "UPDATE review_schedules SET due_date = LEAST(due_date, :today), repetitions = 0, interval_days = 0,
                    version = version + 1, state = IF(state = 'new', 'new', 'learning')
             WHERE user_id = :uid AND word_id IN (" . implode(',', $marks) . ')'
        );
        $st->execute($p);
        return $st->rowCount();
    }

    public static function remove(int $userId, int $scheduleId): bool
    {
        return self::run('DELETE FROM review_schedules WHERE id = :id AND user_id = :u', ['id' => $scheduleId, 'u' => $userId]) === 1;
    }
}
