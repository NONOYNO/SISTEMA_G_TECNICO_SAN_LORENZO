<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(
            "CREATE TABLE `notification_reads` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `notification_id` BIGINT UNSIGNED NOT NULL,
                `user_id` BIGINT UNSIGNED NOT NULL,
                `read_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `notification_reads_notification_id_user_id_unique` (`notification_id`, `user_id`),
                KEY `notification_reads_user_id_read_at_index` (`user_id`, `read_at`),
                CONSTRAINT `notification_reads_notification_id_foreign`
                    FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`)
                    ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `notification_reads_user_id_foreign`
                    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
                    ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS `notification_reads`');
    }
};
