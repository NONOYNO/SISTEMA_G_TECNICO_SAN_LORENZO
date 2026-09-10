<?php

declare(strict_types=1);

/**
 * CLI seeder runner.
 * Usage: php database/seed.php
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from the CLI.\n");
    exit(1);
}

require __DIR__ . '/bootstrap.php';

try {
    $root = database_project_root();
    $env = database_load_env($root);
    $pdo = database_pdo($env);

    echo "CREDENTIALS FOR DEV ONLY\n";
    echo "Seeding database...\n";

    $seeders = [
        'RoleSeeder.php',
        'PermissionSeeder.php',
        'RolePermissionSeeder.php',
        'UserSeeder.php',
    ];

    foreach ($seeders as $file) {
        $path = __DIR__ . '/seeders/' . $file;
        if (!is_file($path)) {
            throw new RuntimeException("Seeder not found: {$file}");
        }

        require_once $path;
        $class = pathinfo($file, PATHINFO_FILENAME);
        if (!class_exists($class)) {
            throw new RuntimeException("Seeder class missing: {$class}");
        }

        echo "Running {$class}... ";
        $seeder = new $class();
        if (!method_exists($seeder, 'run')) {
            throw new RuntimeException("{$class} must implement run(PDO): void");
        }
        $seeder->run($pdo);
        echo "OK\n";
    }

    echo "Done. Seeders completed.\n";
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, 'Seed error: ' . $e->getMessage() . "\n");
    exit(1);
}
