<?php
declare(strict_types=1);

namespace App\Core;

/** Barcha kontrollerlar uchun umumiy yordamchi amallar */
abstract class Controller
{
    protected function view(string $template, array $data = [], int $status = 200): void
    {
        http_response_code($status);
        View::render($template, $data);
    }

    protected function redirect(string $path, array $query = []): never
    {
        header('Location: ' . url($path, $query), true, 303);
        exit;
    }

    /** Faqat tizimga kirgan foydalanuvchi uchun */
    protected function requireAuth(): array
    {
        $user = Auth::user();
        if ($user === null) {
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
                Session::set('_intended', current_path() . (empty($_SERVER['QUERY_STRING']) ? '' : '?' . $_SERVER['QUERY_STRING']));
            }
            Session::flash('info', 'Davom etish uchun tizimga kiring.');
            $this->redirect('/login');
        }
        return $user;
    }

    /** Rolni serverda tekshirish (tugmani yashirish yetarli emas) */
    protected function requireRole(string ...$roles): array
    {
        $user = $this->requireAuth();
        if (!in_array($user['role'], $roles, true)) {
            abort(403);
        }
        return $user;
    }

    /** POST so‘rovdagi CSRF tokenini tekshirish */
    protected function verifyCsrf(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            abort(405);
        }
        if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
            abort(400, 'Yuborilgan ma’lumot hajmi server chegarasidan oshib ketdi (post_max_size).');
        }
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            abort(419);
        }
    }

    protected function input(string $key, string $default = ''): string
    {
        $v = $_POST[$key] ?? $default;
        return is_string($v) ? trim($v) : $default;
    }

    protected function query(string $key, string $default = ''): string
    {
        $v = $_GET[$key] ?? $default;
        return is_string($v) ? trim($v) : $default;
    }

    protected function queryInt(string $key): int
    {
        $v = filter_var($this->query($key), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        return $v === false ? 0 : (int) $v;
    }

    protected function page(): int
    {
        return max(1, $this->queryInt('page'));
    }

    /** Saralash parametrini faqat ruxsat etilgan ro‘yxatdan qabul qilish */
    protected function sortParams(array $allowed, string $default, string $defaultDir = 'asc'): array
    {
        $sort = $this->query('sort');
        $dir = strtolower($this->query('dir'));
        return [
            array_key_exists($sort, $allowed) ? $sort : $default,
            in_array($dir, ['asc', 'desc'], true) ? $dir : $defaultDir,
        ];
    }
}
