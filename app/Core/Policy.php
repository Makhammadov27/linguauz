<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Avtorizatsiya qoidalari (RBAC + yozuvga egalik).
 *  - admin: barcha predmet ma’lumotlari;
 *  - editor (muharrir/o‘qituvchi): o‘zi yaratgan to‘plamlar, ommaviy to‘plam e’lon qila oladi;
 *  - user (o‘quvchi): faqat o‘zining shaxsiy to‘plamlari va o‘z takrorlash rejasi.
 */
final class Policy
{
    public static function canViewCollection(array $collection, ?array $user): bool
    {
        if ($user !== null && ($user['role'] === 'admin' || (int) $collection['owner_id'] === (int) $user['id'])) {
            return true;
        }
        return $collection['status'] === 'active' && (int) $collection['is_public'] === 1;
    }

    public static function canEditCollection(array $collection, ?array $user): bool
    {
        if ($user === null) {
            return false;
        }
        if ($user['role'] === 'admin') {
            return true;
        }
        return (int) $collection['owner_id'] === (int) $user['id'];
    }

    /** Ommaviy (barchaga ochiq) to‘plam yaratish huquqi */
    public static function canPublish(?array $user): bool
    {
        return $user !== null && in_array($user['role'], ['admin', 'editor'], true);
    }
}
