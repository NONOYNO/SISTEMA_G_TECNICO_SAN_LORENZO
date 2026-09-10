<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

use App\Router;

// Servir /assets/* desde app/assets (portable con cualquier DocumentRoot / carpeta).
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$requestPath = is_string($requestPath) ? $requestPath : '/';
$basePath = app_base_path();

if ($basePath !== '' && str_starts_with($requestPath, $basePath)) {
    $requestPath = substr($requestPath, strlen($basePath)) ?: '/';
}

$requestPath = '/' . trim(str_replace('\\', '/', $requestPath), '/');
if ($requestPath !== '/') {
    $requestPath = rtrim($requestPath, '/');
}

if (str_starts_with($requestPath, '/assets/')) {
    serve_app_asset(substr($requestPath, strlen('/assets/')));
}

$router = new Router();

require APP_PATH . '/routes/web.php';

$router->dispatch();
