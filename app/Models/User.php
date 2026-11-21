<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class User extends Model
{
    public const ROLES = [
        'admin'  => 'Administrator',
        'editor' => 'Muharrir (o‘qituvchi)',
        'user'   => 'O‘quvchi',
    ];

    private const SELECT = 'SELECT u.id, u.name, u.email, u.password_hash, u.is_active, u.created_at, r.slug AS role, r.name AS role_name
                            FROM users u JOIN roles r ON r.id = u.role_id';

    public static function findActive(int $id): ?array
    {
        return self::row(self::SELECT . ' WHERE u.id = :id AND u.is_active = 1', ['id' => $id]);
    }

    public static function find(int $id): ?array
    {
        return self::row(self::SELECT . ' WHERE u.id = :id', ['id' => $id]);
    }

    public static function findByEmail(string $email): ?array
    {
        return self::row(self::SELECT . ' WHERE u.email = :email', ['email' => $email]);
    }

    public static function emailExists(string $email, int $exceptId = 0): bool
    {
        return (bool) self::value('SELECT 1 FROM users WHERE email = :e AND id <> :id', ['e' => $email, 'id' => $exceptId]);
    }

    public static function create(string $name, string $email, string $password, string $role): int
    {
        return self::insert(
            'INSERT INTO users (role_id, name, email, password_hash)
             VALUES ((SELECT id FROM roles WHERE slug = :role), :name, :email, :hash)',
            ['role' => $role, 'name' => $name, 'email' => $email, 'hash' => password_hash($password, PASSWORD_DEFAULT)]
        );
    }

    public static function update(int $id, string $name, string $email, string $role, ?string $password): void
    {
        self::run(
            'UPDATE users SET name = :name, email = :email, role_id = (SELECT id FROM roles WHERE slug = :role) WHERE id = :id',
            ['name' => $name, 'email' => $email, 'role' => $role, 'id' => $id]
        );
        if ($password !== null && $password !== '') {
            self::updatePassword($id, $password);
        }
    }

    public static function updatePassword(int $id, string $password): void
    {
        self::run('UPDATE users SET password_hash = :h WHERE id = :id', ['h' => password_hash($password, PASSWORD_DEFAULT), 'id' => $id]);
    }

    public static function setActive(int $id, bool $active): void
    {
        self::run('UPDATE users SET is_active = :a WHERE id = :id', ['a' => $active ? 1 : 0, 'id' => $id]);
    }

    public static function search(string $q, string $role, int $page, int $perPage = 15): array
    {
        $where = ['1=1'];
        $p = [];
        if ($q !== '') {
            $where[] = '(u.name LIKE :q1 OR u.email LIKE :q2)';
            $p['q1'] = $p['q2'] = like_escape($q);
        }
        if (isset(self::ROLES[$role])) {
            $where[] = 'r.slug = :role';
            $p['role'] = $role;
        }
        $w = implode(' AND ', $where);
        $total = (int) self::value("SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE $w", $p);
        [$page, $pages, $offset] = self::paginate($total, $page, $perPage);
        $items = self::rows(
            "SELECT u.id, u.name, u.email, u.is_active, u.created_at, r.slug AS role, r.name AS role_name,
                    (SELECT COUNT(*) FROM collections c WHERE c.owner_id = u.id) AS collections_count
             FROM users u JOIN roles r ON r.id = u.role_id WHERE $w ORDER BY u.id LIMIT $perPage OFFSET $offset",
            $p
        );
        return compact('items', 'total', 'page', 'pages');
    }

    public static function countAll(): int
    {
        return (int) self::value('SELECT COUNT(*) FROM users WHERE is_active = 1');
    }

    public static function countAdmins(): int
    {
        return (int) self::value("SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE r.slug = 'admin' AND u.is_active = 1");
    }
}
