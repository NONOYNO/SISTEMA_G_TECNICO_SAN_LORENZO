<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(
            "CREATE TABLE `announcements` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `author_id` BIGINT UNSIGNED NOT NULL,
                `title` VARCHAR(200) NOT NULL,
                `description` VARCHAR(500) NULL,
                `content` TEXT NOT NULL,
                `category` VARCHAR(100) NULL,
                `priority` ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
                `status` ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
                `publish_at` DATETIME NULL,
                `expire_at` DATETIME NULL,
                `audience` VARCHAR(255) NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `announcements_status_index` (`status`),
                KEY `announcements_publish_at_index` (`publish_at`),
                KEY `announcements_author_id_index` (`author_id`),
                KEY `announcements_priority_index` (`priority`),
                CONSTRAINT `announcements_author_id_foreign`
                    FOREIGN KEY (`author_id`) REFERENCES `users` (`id`)
                    ON DELETE RESTRICT ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS `announcements`');
    }
};
