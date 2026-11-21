<?php
declare(strict_types=1);

namespace App\Core;

/** Ko‘rinishni (HTML shablonni) umumiy maket ichida chiqarish */
final class View
{
    public static function render(string $template, array $data = [], ?string $layout = 'layouts/main'): void
    {
        $content = self::capture($template, $data);
        if ($layout === null) {
            echo $content;
            return;
        }
        echo self::capture($layout, $data + ['content' => $content]);
    }

    public static function capture(string $__template, array $__data): string
    {
        $__file = BASE_PATH . '/app/Views/' . $__template . '.php';
        if (!is_file($__file)) {
            throw new \RuntimeException('Ko‘rinish topilmadi: ' . $__template);
        }
        extract($__data, EXTR_SKIP);
        ob_start();
        try {
            include $__file;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        return (string) ob_get_clean();
    }

    public static function partial(string $name, array $data = []): void
    {
        echo self::capture('partials/' . $name, $data);
    }

    public static function renderError(int $code, string $message = ''): void
    {
        $titles = [
            400 => 'Noto‘g‘ri so‘rov',
            403 => 'Ruxsat berilmagan',
            404 => 'Sahifa topilmadi',
            405 => 'Usul ruxsat etilmagan',
            419 => 'Sahifa muddati tugagan',
            429 => 'Juda ko‘p so‘rov',
            500 => 'Serverda xatolik',
        ];
        $defaults = [
            403 => 'Bu amalni bajarish uchun sizda yetarli huquq yo‘q.',
            404 => 'So‘ralgan sahifa yoki yozuv mavjud emas yoki sizga ochiq emas.',
            419 => 'Xavfsizlik tokeni eskirgan yoki yuborilmagan. Sahifani yangilab, qayta urinib ko‘ring.',
            500 => 'Kutilmagan xatolik yuz berdi. Keyinroq qayta urinib ko‘ring.',
        ];
        if (!headers_sent()) {
            http_response_code($code);
        }
        self::render('errors/error', [
            'code'    => $code,
            'title'   => $titles[$code] ?? 'Xatolik',
            'message' => $message !== '' ? $message : ($defaults[$code] ?? ''),
            'noindex' => true,
        ]);
    }
}
