<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(
            "CREATE TABLE `notifications` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` BIGINT UNSIGNED NOT NULL,
                `announcement_id` BIGINT UNSIGNED NULL,
                `title` VARCHAR(200) NOT NULL,
                `body` TEXT NULL,
                `priority` ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
                `type` VARCHAR(50) NOT NULL DEFAULT 'announcement',
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `notifications_user_id_created_at_index` (`user_id`, `created_at`),
                KEY `notifications_announcement_id_index` (`announcement_id`),
                KEY `notifications_priority_index` (`priority`),
                CONSTRAINT `notifications_user_id_foreign`
                    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
                    ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `notifications_announcement_id_foreign`
                    FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`)
                    ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS `notifications`');
    }
};
