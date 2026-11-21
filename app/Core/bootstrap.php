<?php
declare(strict_types=1);

// PSR-4 uslubidagi oddiy autoloader: App\Core\Router -> app/Core/Router.php
spl_autoload_register(static function (string $class): void {
    if (!str_starts_with($class, 'App\\')) {
        return;
    }
    $file = BASE_PATH . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

$configFile = BASE_PATH . '/config/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    exit('config/config.php topilmadi. config/config.example.php faylidan nusxa olib, sozlamalarni kiriting.');
}
$GLOBALS['__config'] = require $configFile;

require BASE_PATH . '/app/Core/helpers.php';

date_default_timezone_set((string) config('app.timezone', 'Asia/Tashkent'));
mb_internal_encoding('UTF-8');

App\Core\ErrorHandler::register();
App\Core\Session::start();

// Xavfsizlik sarlavhalari
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: same-origin');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; media-src 'self'; "
    . "style-src 'self' 'unsafe-inline'; script-src 'self'; form-action 'self'; frame-ancestors 'self'; base-uri 'self'");
