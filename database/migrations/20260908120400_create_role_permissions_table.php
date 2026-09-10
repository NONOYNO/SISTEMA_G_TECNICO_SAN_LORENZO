<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(
            "CREATE TABLE `role_permissions` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `role_id` BIGINT UNSIGNED NOT NULL,
                `permission_id` BIGINT UNSIGNED NOT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `role_permissions_role_id_permission_id_unique` (`role_id`, `permission_id`),
                KEY `role_permissions_permission_id_index` (`permission_id`),
                CONSTRAINT `role_permissions_role_id_foreign`
                    FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
                    ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `role_permissions_permission_id_foreign`
                    FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`)
                    ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS `role_permissions`');
    }
};
