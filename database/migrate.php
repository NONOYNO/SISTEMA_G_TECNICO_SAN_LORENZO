<?php

declare(strict_types=1);

/**
 * CLI migration runner.
 * Usage: php database/migrate.php
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from the CLI.\n");
    exit(1);
}

require __DIR__ . '/bootstrap.php';

try {
    $root = database_project_root();
    $env = database_load_env($root);
    $dbName = database_env($env, 'DB_DATABASE', 'ue_san_lorenzo');

    echo "Connecting to MySQL...\n";
    $server = database_pdo_server($env);
    database_ensure_database($server, $dbName);
    echo "Database `{$dbName}` ready.\n";

    $pdo = database_pdo($env);

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS `migrations` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `migration` VARCHAR(255) NOT NULL,
            `batch` INT NOT NULL,
            `applied_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `migrations_migration_unique` (`migration`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $applied = [];
    $stmt = $pdo->query('SELECT `migration` FROM `migrations` ORDER BY `id` ASC');
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $name) {
        $applied[$name] = true;
    }

    $files = glob(__DIR__ . '/migrations/*.php') ?: [];
    sort($files, SORT_STRING);

    $pending = [];
    foreach ($files as $file) {
        $basename = basename($file);
        if (!preg_match('/^\d{14}_[a-z0-9_]+\.php$/', $basename)) {
            echo "Skipping invalid migration name: {$basename}\n";
            continue;
        }
        if (!isset($applied[$basename])) {
            $pending[] = $file;
        }
    }

    if ($pending === []) {
        echo "Nothing to migrate. All migrations are applied.\n";
        exit(0);
    }

    $batchStmt = $pdo->query('SELECT COALESCE(MAX(`batch`), 0) FROM `migrations`');
    $batch = (int) $batchStmt->fetchColumn() + 1;

    $insert = $pdo->prepare(
        'INSERT INTO `migrations` (`migration`, `batch`, `applied_at`) VALUES (?, ?, NOW())'
    );

    $count = 0;
    foreach ($pending as $file) {
        $basename = basename($file);
        echo "Migrating: {$basename} ... ";

        /** @var object $migration */
        $migration = require $file;
        if (!is_object($migration) || !method_exists($migration, 'up')) {
            throw new RuntimeException("Migration {$basename} must return a class with up(PDO): void");
        }

        try {
            $migration->up($pdo);
            $insert->execute([$basename, $batch]);
            echo "OK\n";
            $count++;
        } catch (Throwable $e) {
            echo "FAIL\n";
            throw $e;
        }
    }

    echo "Done. Batch {$batch} ({$count} migration(s)).\n";
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, 'Migration error: ' . $e->getMessage() . "\n");
    exit(1);
}
