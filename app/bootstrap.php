<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', __DIR__);

/**
 * Carga variables desde archivo .env (KEY=VALUE).
 */
function load_env_file(string $path): void
{
    if (!is_file($path) || !is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        if (!str_contains($line, '=')) {
            continue;
        }

        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        if ($name === '') {
            continue;
        }

        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"'))
            || (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
        putenv($name . '=' . $value);
    }
}

load_env_file(BASE_PATH . DIRECTORY_SEPARATOR . '.env');

require_once APP_PATH . '/helpers/env.php';
require_once APP_PATH . '/helpers/security.php';
require_once APP_PATH . '/helpers/csrf.php';
require_once APP_PATH . '/helpers/auth.php';
require_once APP_PATH . '/helpers/response.php';

date_default_timezone_set((string) env('APP_TIMEZONE', 'America/Guayaquil'));

$debug = (bool) env('APP_DEBUG', false);

if ($debug) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', '0');
}

ini_set('log_errors', '1');
ini_set('error_log', BASE_PATH . '/logs/php-error.log');

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $parts = explode('\\', $relative);

    if ($parts === []) {
        return;
    }

    // App\Helpers\* → app/helpers/*
    // App\Controllers\* → app/controllers/*
    // App\Services\* → app/Services/* (case preserved if folder exists)
    // App\Repositories\* → app/Repositories/*
    $map = [
        'Helpers' => 'helpers',
        'Controllers' => 'controllers',
        'Middleware' => 'middleware',
        'Services' => 'Services',
        'Repositories' => 'Repositories',
    ];

    $root = $parts[0];
    foreach ($map as $ns => $dir) {
        if (strcasecmp($root, $ns) === 0) {
            $parts[0] = $dir;
            break;
        }
    }

    $path = APP_PATH . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts) . '.php';

    if (is_file($path)) {
        require_once $path;
    }
});

$lifetimeMinutes = (int) env('SESSION_LIFETIME', 120);
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
$cookiePath = app_base_path();
if ($cookiePath === '') {
    $cookiePath = '/';
}

session_name('ue_san_lorenzo_session');
session_set_cookie_params([
    'lifetime' => $lifetimeMinutes * 60,
    'path' => $cookiePath,
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax',
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

$appKey = (string) env('APP_KEY', '');

if ($appKey === '' || $appKey === 'change-me-to-random-32chars-key!!') {
    if ((string) env('APP_ENV', 'production') === 'production') {
        throw new RuntimeException('APP_KEY debe configurarse en producción.');
    }
}
