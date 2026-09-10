<?php

declare(strict_types=1);

/**
 * Shared helpers for migration/seed CLI runners.
 */

function database_project_root(): string
{
    return dirname(__DIR__);
}

/**
 * @return array<string, string>
 */
function database_load_env(string $root): array
{
    $path = $root . DIRECTORY_SEPARATOR . '.env';
    if (!is_file($path)) {
        throw new RuntimeException('.env not found at project root: ' . $path);
    }

    $vars = [];
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        throw new RuntimeException('Unable to read .env');
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }
        $vars[$key] = $value;
        $_ENV[$key] = $value;
        putenv($key . '=' . $value);
    }

    return $vars;
}

/**
 * @param array<string, string> $env
 */
function database_env(array $env, string $key, ?string $default = null): string
{
    if (array_key_exists($key, $env) && $env[$key] !== '') {
        return $env[$key];
    }
    if ($default !== null) {
        return $default;
    }
    throw new RuntimeException("Missing required .env key: {$key}");
}

/**
 * Connect without selecting a database (for CREATE DATABASE).
 *
 * @param array<string, string> $env
 */
function database_pdo_server(array $env): PDO
{
    $host = database_env($env, 'DB_HOST', '127.0.0.1');
    $port = database_env($env, 'DB_PORT', '3306');
    $user = database_env($env, 'DB_USERNAME', 'root');
    $pass = $env['DB_PASSWORD'] ?? '';

    $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";

    return new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}

/**
 * Connect to the application database.
 *
 * @param array<string, string> $env
 */
function database_pdo(array $env): PDO
{
    $host = database_env($env, 'DB_HOST', '127.0.0.1');
    $port = database_env($env, 'DB_PORT', '3306');
    $name = database_env($env, 'DB_DATABASE', 'ue_san_lorenzo');
    $user = database_env($env, 'DB_USERNAME', 'root');
    $pass = $env['DB_PASSWORD'] ?? '';

    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

    return new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}

function database_ensure_database(PDO $server, string $database): void
{
    $quoted = str_replace('`', '``', $database);
    $server->exec(
        "CREATE DATABASE IF NOT EXISTS `{$quoted}`
         CHARACTER SET utf8mb4
         COLLATE utf8mb4_unicode_ci"
    );
}
