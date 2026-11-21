<?php
declare(strict_types=1);

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\View;

/** Konfiguratsiya qiymatini nuqtali kalit orqali olish: config('db.host') */
function config(string $key, mixed $default = null): mixed
{
    $value = $GLOBALS['__config'] ?? [];
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}

/** HTML kontekstida xavfsiz chiqarish (XSS himoyasi) */
function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Ilova joylashgan bazaviy yo‘l (masalan /linguauz/public yoki bo‘sh satr) */
function base_path_url(): string
{
    static $base = null;
    if ($base === null) {
        $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
        $base = rtrim($dir, '/.');
    }
    return $base;
}

function url(string $path = '/', array $query = []): string
{
    $url = base_path_url() . '/' . ltrim($path, '/');
    $query = array_filter($query, static fn($v) => $v !== null && $v !== '' && !is_array($v));
    if ($query) {
        $url .= '?' . http_build_query($query);
    }
    return $url;
}

function asset(string $path): string
{
    $file = BASE_PATH . '/public/assets/' . ltrim($path, '/');
    $version = is_file($file) ? (string) filemtime($file) : '1';
    return url('assets/' . ltrim($path, '/')) . '?v=' . $version;
}

/** Joriy so‘rov yo‘li (bazaviy yo‘lsiz) */
function current_path(): string
{
    $path = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
    $base = base_path_url();
    if ($base !== '' && str_starts_with($path, $base)) {
        $path = substr($path, strlen($base));
    }
    return '/' . trim($path, '/');
}

/** Joriy GET parametrlarini o‘zgartirib, havola yasash (saralash va sahifalash uchun) */
function page_url(array $changes = [], ?string $path = null): string
{
    $query = array_filter($_GET, static fn($v) => is_string($v));
    $query = array_merge($query, $changes);
    return url($path ?? current_path(), $query);
}

function nav_active(string $prefix): string
{
    $path = current_path();
    $active = $prefix === '/' ? $path === '/' : str_starts_with($path, $prefix);
    return $active ? ' aria-current="page"' : '';
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(Csrf::token()) . '">';
}

function abort(int $code, string $message = ''): never
{
    View::renderError($code, $message);
    exit;
}

function current_user(): ?array
{
    return Auth::user();
}

function has_role(string ...$roles): bool
{
    return Auth::hasRole(...$roles);
}

function fmt_date(?string $date, string $format = 'd.m.Y'): string
{
    if (!$date) {
        return '—';
    }
    try {
        return (new DateTimeImmutable($date))->format($format);
    } catch (Exception) {
        return '—';
    }
}

function human_size(int $bytes): string
{
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 1, ',', ' ') . ' MB';
    }
    if ($bytes >= 1024) {
        return number_format($bytes / 1024, 0, ',', ' ') . ' KB';
    }
    return $bytes . ' B';
}

/** LIKE uchun maxsus belgilarni ekranlash */
function like_escape(string $value): string
{
    return '%' . addcslashes($value, '%_\\') . '%';
}

/** Saralanadigan jadval sarlavhasi uchun havola */
function sort_link(string $label, string $key, string $currentSort, string $currentDir): string
{
    $newDir = ($currentSort === $key && $currentDir === 'asc') ? 'desc' : 'asc';
    $arrow = $currentSort === $key ? ($currentDir === 'asc' ? ' ▲' : ' ▼') : '';
    $href = page_url(['sort' => $key, 'dir' => $newDir, 'page' => 1]);
    return '<a href="' . e($href) . '">' . e($label) . '<span aria-hidden="true">' . $arrow . '</span></a>';
}

function aria_sort(string $key, string $currentSort, string $currentDir): string
{
    if ($currentSort !== $key) {
        return '';
    }
    return ' aria-sort="' . ($currentDir === 'asc' ? 'ascending' : 'descending') . '"';
}

/** Mijozning IP manzili (cheklovlar uchun) */
function client_ip(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45);
}

/** So‘z shaklini sonlarga moslash: 5 ta so‘z */
function plural_days(int $days): string
{
    return $days . ' kun';
}
