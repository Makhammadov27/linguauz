<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Yo‘naltiruvchi: so‘rov usuli va manziliga mos kontroller amalini tanlaydi.
 * {id} kabi parametrlar faqat raqam bo‘lishi mumkin.
 */
final class Router
{
    /** @var array<int, array{0:string,1:string,2:array{0:class-string,1:string}}> */
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    private function add(string $method, string $path, array $handler): void
    {
        $regex = '#^' . preg_replace('#\{([a-z_]+)\}#', '(?P<$1>[0-9]{1,10})', $path) . '$#';
        $this->routes[] = [$method, $regex, $handler];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = current_path();
        if (str_starts_with($path, '/index.php')) {
            $path = '/' . ltrim(substr($path, 10), '/');
        }
        $method = strtoupper($method) === 'HEAD' ? 'GET' : strtoupper($method);

        $allowed = [];
        foreach ($this->routes as [$routeMethod, $regex, $handler]) {
            if (!preg_match($regex, $path, $matches)) {
                continue;
            }
            if ($routeMethod !== $method) {
                $allowed[] = $routeMethod;
                continue;
            }
            $params = [];
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[] = (int) $value;
                }
            }
            [$class, $action] = $handler;
            (new $class())->$action(...$params);
            return;
        }

        if ($allowed) {
            header('Allow: ' . implode(', ', array_unique($allowed)));
            abort(405, 'Bu manzil uchun ' . $method . ' usuli ruxsat etilmagan.');
        }
        abort(404);
    }
}
