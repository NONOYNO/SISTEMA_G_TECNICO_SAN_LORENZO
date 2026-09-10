<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(
            "CREATE TABLE `audit_logs` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` BIGINT UNSIGNED NULL,
                `action` VARCHAR(100) NOT NULL,
                `entity` VARCHAR(50) NULL,
                `entity_id` BIGINT NULL,
                `old_values` TEXT NULL,
                `new_values` TEXT NULL,
                `ip` VARCHAR(45) NULL,
                `user_agent` VARCHAR(255) NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `audit_logs_user_id_index` (`user_id`),
                KEY `audit_logs_action_index` (`action`),
                KEY `audit_logs_created_at_index` (`created_at`),
                CONSTRAINT `audit_logs_user_id_foreign`
                    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
                    ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS `audit_logs`');
    }
};
