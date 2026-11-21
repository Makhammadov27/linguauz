<?php
/**
 * LinguaUz — yagona kirish nuqtasi (front controller).
 * Barcha so‘rovlar shu faylga yo‘naltiriladi va Router orqali kontrollerga uzatiladi.
 */
declare(strict_types=1);

// PHP ichki serverida (php -S) mavjud statik fayllarni to‘g‘ridan-to‘g‘ri berish
if (PHP_SAPI === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if ($path !== '/' && is_file(__DIR__ . $path)) {
        return false;
    }
}

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/app/Core/bootstrap.php';

/** @var App\Core\Router $router */
$router = require BASE_PATH . '/app/routes.php';
$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
