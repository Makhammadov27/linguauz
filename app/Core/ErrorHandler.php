<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Xatolarni qayd etish. Ish muhitida (debug=false) texnik tafsilotlar foydalanuvchiga ko‘rsatilmaydi,
 * faqat storage/logs/app.log fayliga yoziladi.
 */
final class ErrorHandler
{
    public static function register(): void
    {
        $debug = (bool) config('app.debug', false);
        error_reporting(E_ALL);
        ini_set('display_errors', $debug ? '1' : '0');
        ini_set('log_errors', '1');
        ini_set('error_log', BASE_PATH . '/storage/logs/php-error.log');

        set_error_handler(static function (int $no, string $str, string $file, int $line): bool {
            if (!(error_reporting() & $no)) {
                return false;
            }
            throw new \ErrorException($str, 0, $no, $file, $line);
        });
        set_exception_handler([self::class, 'handle']);
    }

    public static function handle(\Throwable $e): void
    {
        self::log($e);
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        if (!headers_sent()) {
            http_response_code(500);
        }
        if (config('app.debug')) {
            echo '<pre style="padding:1rem;white-space:pre-wrap">' . e((string) $e) . '</pre>';
            return;
        }
        try {
            View::renderError(500);
        } catch (\Throwable) {
            echo 'Serverda xatolik yuz berdi.';
        }
    }

    public static function log(\Throwable $e): void
    {
        $line = sprintf(
            "[%s] %s: %s (%s:%d) URI=%s\n",
            date('Y-m-d H:i:s'),
            $e::class,
            $e->getMessage(),
            str_replace(BASE_PATH, '', $e->getFile()),
            $e->getLine(),
            substr((string) ($_SERVER['REQUEST_URI'] ?? '-'), 0, 200)
        );
        @file_put_contents(BASE_PATH . '/storage/logs/app.log', $line, FILE_APPEND | LOCK_EX);
    }

    /** Xavfsiz amaliy qayd (masalan pochta natijasi). Parol va token yozilmaydi. */
    public static function info(string $message): void
    {
        $line = sprintf("[%s] INFO: %s\n", date('Y-m-d H:i:s'), $message);
        @file_put_contents(BASE_PATH . '/storage/logs/app.log', $line, FILE_APPEND | LOCK_EX);
    }
}
