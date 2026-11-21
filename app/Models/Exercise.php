<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;
use PDO;

/** Mashq (test) va urinishlar */
final class Exercise extends Model
{
    public const TYPES = ['choice' => 'Variantli test', 'typing' => 'Yozma javob'];
    public const DIRECTIONS = ['to_uz' => 'Chet tilidan o‘zbekchaga', 'to_foreign' => 'O‘zbekchadan chet tiliga'];
    public const SORTS = ['created' => 'e.created_at', 'title' => 'e.title', 'questions' => 'e.question_count'];

    public static function search(array $f, ?array $user, int $page, int $perPage = 12): array
    {
        $p = [];
        $where = [Collection::visibility($user, $p)];
        if ($f['q'] !== '') {
            $where[] = '(e.title LIKE :q1 OR c.title LIKE :q2)';
            $p['q1'] = $p['q2'] = like_escape($f['q']);
        }
        if ($f['language_id'] > 0) {
            $where[] = 'c.language_id = :lang';
            $p['lang'] = $f['language_id'];
        }
        if (isset(self::TYPES[$f['type']])) {
            $where[] = 'e.type = :type';
            $p['type'] = $f['type'];
        }
        $w = implode(' AND ', $where);
        $from = 'FROM exercises e JOIN collections c ON c.id = e.collection_id JOIN languages l ON l.id = c.language_id';
        $total = (int) self::value("SELECT COUNT(*) $from WHERE $w", $p);
        [$page, $pages, $offset] = self::paginate($total, $page, $perPage);
        $order = (self::SORTS[$f['sort']] ?? 'e.created_at') . ($f['dir'] === 'asc' ? ' ASC' : ' DESC');

        $best = '';
        if ($user !== null) {
            $best = ', (SELECT MAX(a.percent) FROM exercise_attempts a WHERE a.exercise_id = e.id AND a.user_id = :me) AS best_percent';
            $p['me'] = (int) $user['id'];
        }
        $items = self::rows(
            "SELECT e.*, c.title AS collection_title, c.level, l.name AS language_name,
                    (SELECT COUNT(*) FROM words w WHERE w.collection_id = e.collection_id) AS words_count $best
             $from WHERE $w ORDER BY $order, e.id DESC LIMIT $perPage OFFSET $offset",
            $p
        );
        return compact('items', 'total', 'page', 'pages');
    }

    public static function find(int $id): ?array
    {
        return self::row(
            'SELECT e.*, c.title AS collection_title, c.owner_id, c.is_public, c.status, c.level,
                    l.name AS language_name, l.speech_code, u.name AS creator_name,
                    (SELECT COUNT(*) FROM words w WHERE w.collection_id = e.collection_id) AS words_count
             FROM exercises e
             JOIN collections c ON c.id = e.collection_id
             JOIN languages l ON l.id = c.language_id
             JOIN users u ON u.id = e.created_by
             WHERE e.id = :id',
            ['id' => $id]
        );
    }

    public static function forCollection(int $collectionId): array
    {
        return self::rows('SELECT * FROM exercises WHERE collection_id = :c ORDER BY title', ['c' => $collectionId]);
    }

    public static function create(array $d, int $userId): int
    {
        return self::insert(
            'INSERT INTO exercises (collection_id, created_by, title, type, direction, question_count)
             VALUES (:c, :u, :t, :type, :dir, :n)',
            ['c' => $d['collection_id'], 'u' => $userId, 't' => $d['title'], 'type' => $d['type'], 'dir' => $d['direction'], 'n' => $d['question_count']]
        );
    }

    public static function update(int $id, array $d): void
    {
        self::run(
            'UPDATE exercises SET collection_id = :c, title = :t, type = :type, direction = :dir, question_count = :n WHERE id = :id',
            ['c' => $d['collection_id'], 't' => $d['title'], 'type' => $d['type'], 'dir' => $d['direction'], 'n' => $d['question_count'], 'id' => $id]
        );
    }

    public static function delete(int $id): void
    {
        self::run('DELETE FROM exercises WHERE id = :id', ['id' => $id]);
    }

    /** Savollar: to‘plamdan tasodifiy so‘zlar; variantli testda 3 ta chalg‘ituvchi javob */
    public static function buildQuiz(array $ex): array
    {
        $all = self::rows('SELECT id, term, translation FROM words WHERE collection_id = :c', ['c' => $ex['collection_id']]);
        shuffle($all);
        $picked = array_slice($all, 0, (int) $ex['question_count']);
        $toUz = $ex['direction'] === 'to_uz';
        $questions = [];
        foreach ($picked as $w) {
            $answer = $toUz ? $w['translation'] : $w['term'];
            $options = [];
            if ($ex['type'] === 'choice') {
                $pool = [];
                foreach ($all as $o) {
                    $candidate = $toUz ? $o['translation'] : $o['term'];
                    if ((int) $o['id'] !== (int) $w['id'] && self::normalize($candidate) !== self::normalize($answer)) {
                        $pool[$candidate] = true;
                    }
                }
                $pool = array_keys($pool);
                shuffle($pool);
                $options = array_slice($pool, 0, 3);
                $options[] = $answer;
                shuffle($options);
            }
            $questions[] = ['word_id' => (int) $w['id'], 'prompt' => $toUz ? $w['term'] : $w['translation'], 'options' => $options];
        }
        return $questions;
    }

    /** Javoblarni solishtirish uchun normallashtirish: kichik harf, apostrof turlari, ortiqcha bo‘shliq va tinish belgilari */
    public static function normalize(string $s): string
    {
        $s = mb_strtolower(trim($s));
        $s = str_replace(['‘', '’', 'ʻ', 'ʼ', '`', '´'], "'", $s);
        $s = preg_replace('/[.!?¿¡]+$/u', '', $s) ?? $s;
        $s = preg_replace('/\s+/u', ' ', $s) ?? $s;
        return trim($s);
    }

    /** "yuk, bagaj" kabi bir nechta ma’noli javobda istalgan variant qabul qilinadi */
    public static function isCorrect(string $given, string $expected): bool
    {
        $g = self::normalize($given);
        if ($g === '') {
            return false;
        }
        if ($g === self::normalize($expected)) {
            return true;
        }
        foreach (preg_split('/[,;\/]/u', $expected) ?: [] as $variant) {
            if ($g === self::normalize($variant)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Urinishni tranzaksiyada saqlash: urinish + har bir javob + xato so‘zlarni takrorlashga qaytarish.
     * @param array<int,string> $answers word_id => berilgan javob
     */
    public static function saveAttempt(array $ex, int $userId, array $wordIds, array $answers, int $startedAt): array
    {
        $toUz = $ex['direction'] === 'to_uz';
        $words = [];
        if ($wordIds) {
            $marks = [];
            $p = ['c' => $ex['collection_id']];
            foreach (array_values($wordIds) as $i => $id) {
                $marks[] = ':w' . $i;
                $p['w' . $i] = (int) $id;
            }
            foreach (self::rows('SELECT id, term, translation FROM words WHERE collection_id = :c AND id IN (' . implode(',', $marks) . ')', $p) as $w) {
                $words[(int) $w['id']] = $w;
            }
        }

        $rows = [];
        $score = 0;
        $wrong = [];
        foreach ($wordIds as $id) {
            if (!isset($words[$id])) {
                continue; // savol berilgandan keyin so‘z o‘chirilgan bo‘lsa
            }
            $w = $words[$id];
            $expected = $toUz ? $w['translation'] : $w['term'];
            $given = mb_substr(trim((string) ($answers[$id] ?? '')), 0, 200);
            $ok = self::isCorrect($given, $expected);
            $score += $ok ? 1 : 0;
            if (!$ok) {
                $wrong[] = $id;
            }
            $rows[] = ['word_id' => $id, 'prompt' => $toUz ? $w['term'] : $w['translation'], 'expected' => $expected, 'given' => $given, 'ok' => $ok];
        }
        $total = count($rows);
        if ($total === 0) {
            throw new \DomainException('Mashq savollari endi mavjud emas.');
        }
        $percent = round($score * 100 / $total, 2);

        return Database::transaction(function (PDO $pdo) use ($ex, $userId, $score, $total, $percent, $startedAt, $rows, $wrong): array {
            $st = $pdo->prepare(
                'INSERT INTO exercise_attempts (exercise_id, user_id, score, total, percent, started_at)
                 VALUES (:e, :u, :s, :t, :p, :st)'
            );
            $st->execute(['e' => $ex['id'], 'u' => $userId, 's' => $score, 't' => $total, 'p' => $percent, 'st' => date('Y-m-d H:i:s', $startedAt)]);
            $attemptId = (int) $pdo->lastInsertId();

            $ins = $pdo->prepare(
                'INSERT INTO attempt_answers (attempt_id, word_id, prompt, expected, given_answer, is_correct)
                 VALUES (:a, :w, :p, :e, :g, :c)'
            );
            foreach ($rows as $r) {
                $ins->execute(['a' => $attemptId, 'w' => $r['word_id'], 'p' => mb_substr($r['prompt'], 0, 200), 'e' => mb_substr($r['expected'], 0, 200), 'g' => $r['given'], 'c' => $r['ok'] ? 1 : 0]);
            }
            $moved = Review::bringForward($pdo, $userId, $wrong);
            return ['attempt_id' => $attemptId, 'moved' => $moved];
        });
    }

    public static function attempt(int $id): ?array
    {
        return self::row(
            'SELECT a.*, e.title AS exercise_title, e.type, e.direction, e.collection_id, c.title AS collection_title, c.owner_id, u.name AS user_name
             FROM exercise_attempts a
             JOIN exercises e ON e.id = a.exercise_id
             JOIN collections c ON c.id = e.collection_id
             JOIN users u ON u.id = a.user_id
             WHERE a.id = :id',
            ['id' => $id]
        );
    }

    public static function attemptAnswers(int $attemptId): array
    {
        return self::rows('SELECT * FROM attempt_answers WHERE attempt_id = :a ORDER BY id', ['a' => $attemptId]);
    }

    public static function userAttempts(int $userId, int $exerciseId = 0, int $limit = 10): array
    {
        $p = ['u' => $userId];
        $extra = '';
        if ($exerciseId > 0) {
            $extra = ' AND a.exercise_id = :e';
            $p['e'] = $exerciseId;
        }
        return self::rows(
            "SELECT a.*, e.title AS exercise_title FROM exercise_attempts a JOIN exercises e ON e.id = a.exercise_id
             WHERE a.user_id = :u $extra ORDER BY a.finished_at DESC LIMIT " . (int) $limit,
            $p
        );
    }
}
