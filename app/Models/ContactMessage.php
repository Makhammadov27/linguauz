<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class ContactMessage extends Model
{
    public const STATUSES = ['pending' => 'Navbatda', 'sent' => 'Yuborildi', 'failed' => 'Xatolik'];

    public static function create(string $name, string $email, string $message, string $ip): int
    {
        return self::insert(
            'INSERT INTO contact_messages (name, email, message, ip) VALUES (:n, :e, :m, :ip)',
            ['n' => $name, 'e' => $email, 'm' => $message, 'ip' => $ip]
        );
    }

    public static function setMailStatus(int $id, string $status, ?string $error = null): void
    {
        self::run(
            'UPDATE contact_messages SET mail_status = :s, mail_error = :err WHERE id = :id',
            ['s' => $status, 'err' => $error !== null ? mb_substr($error, 0, 255) : null, 'id' => $id]
        );
    }

    public static function recentCountByIp(string $ip, int $minutes): int
    {
        return (int) self::value(
            "SELECT COUNT(*) FROM contact_messages WHERE ip = :ip AND created_at > NOW() - INTERVAL $minutes MINUTE",
            ['ip' => $ip]
        );
    }

    public static function find(int $id): ?array
    {
        return self::row('SELECT * FROM contact_messages WHERE id = :id', ['id' => $id]);
    }

    public static function search(string $status, int $page, int $perPage = 10): array
    {
        $where = '1=1';
        $p = [];
        if (isset(self::STATUSES[$status])) {
            $where = 'mail_status = :s';
            $p['s'] = $status;
        }
        $total = (int) self::value("SELECT COUNT(*) FROM contact_messages WHERE $where", $p);
        [$page, $pages, $offset] = self::paginate($total, $page, $perPage);
        $items = self::rows("SELECT * FROM contact_messages WHERE $where ORDER BY created_at DESC, id DESC LIMIT $perPage OFFSET $offset", $p);
        return compact('items', 'total', 'page', 'pages');
    }

    public static function markRead(int $id): void
    {
        self::run('UPDATE contact_messages SET is_read = 1 WHERE id = :id', ['id' => $id]);
    }

    public static function delete(int $id): void
    {
        self::run('DELETE FROM contact_messages WHERE id = :id', ['id' => $id]);
    }

    public static function unreadCount(): int
    {
        return (int) self::value('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0');
    }
}
