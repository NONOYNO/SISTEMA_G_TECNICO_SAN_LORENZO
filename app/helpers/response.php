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

/**
 * Prefijo de ruta del front controller (ej. "" o "/mi-portal/app/public").
 * No depende del nombre de carpeta del proyecto.
 */
function app_base_path(): string
{
    static $resolved = null;

    if ($resolved !== null) {
        return $resolved;
    }

    $configured = trim((string) env('APP_URL', ''));
    if ($configured !== '') {
        $fromConfig = parse_url($configured, PHP_URL_PATH);
        if (is_string($fromConfig) && $fromConfig !== '' && $fromConfig !== '/') {
            $resolved = rtrim(str_replace('\\', '/', $fromConfig), '/');

            return $resolved;
        }
        if (is_string($fromConfig) && ($fromConfig === '' || $fromConfig === '/')) {
            $resolved = '';

            return $resolved;
        }
    }

    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    if (!is_string($scriptName) || $scriptName === '') {
        $resolved = '';

        return $resolved;
    }

    $dir = str_replace('\\', '/', dirname($scriptName));
    if ($dir === '/' || $dir === '\\' || $dir === '.' || $dir === '') {
        $resolved = '';

        return $resolved;
    }

    $resolved = rtrim($dir, '/');

    return $resolved;
}

/**
 * URL absoluta base de la app (esquema + host + base path).
 * Si APP_URL está vacío, se detecta desde la petición actual.
 */
function app_base_url(): string
{
    static $resolved = null;

    if ($resolved !== null) {
        return $resolved;
    }

    $configured = trim((string) env('APP_URL', ''));
    if ($configured !== '') {
        $resolved = rtrim($configured, '/');

        return $resolved;
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ((string) ($_SERVER['SERVER_PORT'] ?? '') === '443')
        || (strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https');

    $scheme = $https ? 'https' : 'http';
    $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $path = app_base_path();

    $resolved = $scheme . '://' . $host . $path;

    return $resolved;
}

function url(string $path = ''): string
{
    $base = rtrim(app_base_url(), '/');
    $path = trim($path);

    if ($path === '' || $path === '/') {
        return $base . '/';
    }

    return $base . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    $path = ltrim(str_replace('\\', '/', $path), '/');
    $base = rtrim(app_base_url(), '/');
    $url = $base . '/assets/' . $path;

    $localPublic = app_path('public/assets/' . str_replace('/', DIRECTORY_SEPARATOR, $path));
    $localApp = app_path('assets/' . str_replace('/', DIRECTORY_SEPARATOR, $path));

    if (is_file($localPublic)) {
        $url .= '?v=' . (string) filemtime($localPublic);
    } elseif (is_file($localApp)) {
        $url .= '?v=' . (string) filemtime($localApp);
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

/**
 * Sirve un asset estático desde app/assets o app/public/assets.
 */
function serve_app_asset(string $relativePath): void
{
    $relativePath = str_replace('\\', '/', $relativePath);
    $relativePath = ltrim($relativePath, '/');

    if ($relativePath === '' || str_contains($relativePath, '..')) {
        http_response_code(404);
        echo 'Asset no encontrado.';
        exit;
    }

    $candidates = [
        app_path('public/assets/' . str_replace('/', DIRECTORY_SEPARATOR, $relativePath)),
        app_path('assets/' . str_replace('/', DIRECTORY_SEPARATOR, $relativePath)),
    ];

    $file = null;
    foreach ($candidates as $candidate) {
        if (is_file($candidate)) {
            $file = $candidate;
            break;
        }
    }

    if ($file === null) {
        http_response_code(404);
        echo 'Asset no encontrado.';
        exit;
    }

    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $types = [
        'css' => 'text/css; charset=utf-8',
        'js' => 'application/javascript; charset=utf-8',
        'mjs' => 'application/javascript; charset=utf-8',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'map' => 'application/json',
    ];

    header('Content-Type: ' . ($types[$ext] ?? 'application/octet-stream'));
    header('Cache-Control: public, max-age=86400');
    header('X-Content-Type-Options: nosniff');
    readfile($file);
    exit;
}
