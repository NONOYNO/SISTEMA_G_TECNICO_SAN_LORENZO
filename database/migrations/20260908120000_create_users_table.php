<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(
            "CREATE TABLE `users` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `username` VARCHAR(50) NOT NULL,
                `email` VARCHAR(150) NOT NULL,
                `password` VARCHAR(255) NOT NULL,
                `first_name` VARCHAR(100) NOT NULL,
                `last_name` VARCHAR(100) NOT NULL,
                `phone` VARCHAR(30) NULL,
                `status` ENUM('active','inactive','pending') NOT NULL DEFAULT 'pending',
                `last_login_at` DATETIME NULL,
                `failed_login_attempts` INT NOT NULL DEFAULT 0,
                `locked_until` DATETIME NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `users_username_unique` (`username`),
                UNIQUE KEY `users_email_unique` (`email`),
                KEY `users_status_index` (`status`),
                KEY `users_name_index` (`last_name`, `first_name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS `users`');
    }
};
