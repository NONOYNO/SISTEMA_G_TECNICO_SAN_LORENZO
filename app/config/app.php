<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'Portal UE San Lorenzo'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost/PortalInfor/app/public'),
    'key' => env('APP_KEY', ''),
    'timezone' => 'America/Guayaquil',
    'locale' => 'es',
    'session_lifetime' => (int) env('SESSION_LIFETIME', 120),
    'upload_max_mb' => (int) env('UPLOAD_MAX_MB', 512),
];
