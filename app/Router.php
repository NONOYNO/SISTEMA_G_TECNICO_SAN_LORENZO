<?php

declare(strict_types=1);

namespace App;

use Closure;
use RuntimeException;

final class Router
{
    /** @var array<int, array{methods:array<int,string>,path:string,handler:mixed,middleware:array<int,string>}> */
    private array $routes = [];

    /** @var array<string, class-string> */
    private array $middlewareMap = [
        'auth' => \App\Middleware\AuthMiddleware::class,
        'guest' => \App\Middleware\GuestMiddleware::class,
        'csrf' => \App\Middleware\CsrfMiddleware::class,
    ];

    public function get(string $path, mixed $handler, array $middleware = []): self
    {
        return $this->add(['GET'], $path, $handler, $middleware);
    }

    public function post(string $path, mixed $handler, array $middleware = []): self
    {
        return $this->add(['POST'], $path, $handler, $middleware);
    }

    public function match(array $methods, string $path, mixed $handler, array $middleware = []): self
    {
        $normalized = array_map(static fn (string $m): string => strtoupper($m), $methods);

        return $this->add($normalized, $path, $handler, $middleware);
    }

    private function add(array $methods, string $path, mixed $handler, array $middleware): self
    {
        $this->routes[] = [
            'methods' => $methods,
            'path' => $this->normalizePath($path),
            'handler' => $handler,
            'middleware' => $middleware,
        ];

        return $this;
    }

    public function dispatch(?string $method = null, ?string $uri = null): void
    {
        $method = strtoupper($method ?? ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $uri = $this->normalizePath($uri ?? $this->resolveUri());

        foreach ($this->routes as $route) {
            if (!in_array($method, $route['methods'], true)) {
                continue;
            }

            $params = $this->matchPath($route['path'], $uri);

            if ($params === null) {
                continue;
            }

            $pipeline = array_reduce(
                array_reverse($route['middleware']),
                function (Closure $next, string $middlewareName): Closure {
                    return function (array $params) use ($next, $middlewareName): void {
                        $this->runMiddleware($middlewareName, $params, $next);
                    };
                },
                function (array $params) use ($route): void {
                    $this->invoke($route['handler'], $params);
                }
            );

            $pipeline($params);

            return;
        }

        abort(404, 'La página solicitada no existe.');
    }

    private function runMiddleware(string $name, array $params, Closure $next): void
    {
        if (str_starts_with($name, 'permission:')) {
            $permission = substr($name, strlen('permission:'));
            $middleware = new \App\Middleware\PermissionMiddleware($permission);
            $middleware->handle($params, $next);

            return;
        }

        if (!isset($this->middlewareMap[$name])) {
            throw new RuntimeException('Middleware no registrado: ' . $name);
        }

        $class = $this->middlewareMap[$name];
        $middleware = new $class();
        $middleware->handle($params, $next);
    }

    private function invoke(mixed $handler, array $params): void
    {
        if ($handler instanceof Closure) {
            $handler(...array_values($params));

            return;
        }

        if (is_array($handler) && count($handler) === 2) {
            [$class, $action] = $handler;
            $controller = is_object($class) ? $class : new $class();
            $controller->{$action}(...array_values($params));

            return;
        }

        if (is_string($handler) && str_contains($handler, '@')) {
            [$class, $action] = explode('@', $handler, 2);

            if (!str_contains($class, '\\')) {
                $class = 'App\\Controllers\\' . $class;
            }

            $controller = new $class();
            $controller->{$action}(...array_values($params));

            return;
        }

        throw new RuntimeException('Handler de ruta inválido.');
    }

    private function resolveUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH);

        if (!is_string($path) || $path === '') {
            $path = '/';
        }

        $scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        $bases = array_unique(array_filter([
            $scriptName,
            '/PortalInfor/app/public',
            '/portalinfor/app/public',
        ]));

        foreach ($bases as $base) {
            $base = rtrim(str_replace('\\', '/', (string) $base), '/');

            if ($base !== '' && str_starts_with($path, $base)) {
                $path = substr($path, strlen($base)) ?: '/';
                break;
            }
        }

        return $this->normalizePath($path);
    }

    private function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    }

    /**
     * @return array<string, string>|null
     */
    private function matchPath(string $routePath, string $uri): ?array
    {
        if ($routePath === $uri) {
            return [];
        }

        $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $routePath);

        if ($pattern === null) {
            return null;
        }

        $regex = '#^' . $pattern . '$#';

        if (!preg_match($regex, $uri, $matches)) {
            return null;
        }

        $params = [];

        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[$key] = $value;
            }
        }

        return $params;
    }
}
