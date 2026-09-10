<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'Portal UE San Lorenzo'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    // Vacío = detectar automáticamente desde la URL de despliegue (sin depender del nombre de carpeta).
    'url' => env('APP_URL', ''),
    'key' => env('APP_KEY', ''),
    'timezone' => 'America/Guayaquil',
    'locale' => 'es',
    'session_lifetime' => (int) env('SESSION_LIFETIME', 120),
    'upload_max_mb' => (int) env('UPLOAD_MAX_MB', 512),
];
