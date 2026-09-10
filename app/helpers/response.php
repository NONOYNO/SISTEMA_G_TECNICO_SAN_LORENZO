<?php

declare(strict_types=1);

function base_path(string $path = ''): string
{
    $base = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2);

    return $path === '' ? $base : $base . DIRECTORY_SEPARATOR . ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
}

function app_path(string $path = ''): string
{
    $base = defined('APP_PATH') ? APP_PATH : dirname(__DIR__);

    return $path === '' ? $base : $base . DIRECTORY_SEPARATOR . ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
}

function config(string $key, mixed $default = null): mixed
{
    static $cache = [];

    $segments = explode('.', $key);
    $file = array_shift($segments);

    if ($file === null || $file === '') {
        return $default;
    }

    if (!isset($cache[$file])) {
        $path = app_path('config/' . $file . '.php');
        $cache[$file] = is_file($path) ? require $path : [];
    }

    $value = $cache[$file];

    foreach ($segments as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }

    return $value;
}

function url(string $path = ''): string
{
    $base = rtrim((string) config('app.url', env('APP_URL', '')), '/');
    $path = '/' . ltrim($path, '/');

    if ($path === '/') {
        return $base . '/';
    }

    return $base . $path;
}

function asset(string $path): string
{
    $base = rtrim((string) config('app.url', env('APP_URL', '')), '/');
    $assetsBase = preg_replace('#/public$#', '/assets', $base) ?: ($base . '/../assets');
    $url = rtrim((string) $assetsBase, '/') . '/' . ltrim($path, '/');

    $local = app_path('assets/' . ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR));
    if (is_file($local)) {
        $url .= '?v=' . (string) filemtime($local);
    }

    return $url;
}

function view(string $name, array $data = [], ?string $layout = 'main'): void
{
    $viewFile = app_path('views/' . str_replace('.', '/', $name) . '.php');

    if (!is_file($viewFile)) {
        http_response_code(500);
        echo 'Vista no encontrada: ' . e($name);
        return;
    }

    extract($data, EXTR_SKIP);

    ob_start();
    require $viewFile;
    $content = ob_get_clean() ?: '';

    if ($layout === null) {
        echo $content;
        return;
    }

    $layoutFile = app_path('views/layouts/' . $layout . '.php');

    if (!is_file($layoutFile)) {
        http_response_code(500);
        echo 'Layout no encontrado: ' . e($layout);
        return;
    }

    require $layoutFile;
}

function redirect(string $path, int $status = 302): void
{
    if (preg_match('#^https?://#i', $path) === 1) {
        $location = $path;
    } else {
        $location = url($path);
    }

    header('Location: ' . $location, true, $status);
    exit;
}

function json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');

    $defaults = [
        'success' => $status >= 200 && $status < 300,
        'message' => '',
        'data' => null,
        'errors' => (object) [],
    ];

    echo json_encode(array_merge($defaults, $payload), JSON_UNESCAPED_UNICODE);
    exit;
}

function flash(string $key, mixed $value = null): mixed
{
    if ($value !== null) {
        $_SESSION['_flash'][$key] = $value;
        return null;
    }

    if (!isset($_SESSION['_flash'][$key])) {
        return null;
    }

    $stored = $_SESSION['_flash'][$key];
    unset($_SESSION['_flash'][$key]);

    return $stored;
}

function old(string $key, mixed $default = ''): mixed
{
    $old = $_SESSION['_old_input'][$key] ?? $default;

    return $old;
}

function flash_input(array $input): void
{
    $_SESSION['_old_input'] = $input;
}

function clear_old_input(): void
{
    unset($_SESSION['_old_input']);
}

function abort(int $status, string $message = ''): void
{
    http_response_code($status);

    $view = match ($status) {
        403 => 'errors.403',
        404 => 'errors.404',
        default => 'errors.404',
    };

    $layout = auth_check() ? 'main' : 'auth';

    view($view, [
        'title' => (string) $status,
        'message' => $message,
    ], $layout);

    exit;
}
