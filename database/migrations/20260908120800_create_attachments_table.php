<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(
            "CREATE TABLE `attachments` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `uploaded_by` BIGINT UNSIGNED NOT NULL,
                `attachable_type` VARCHAR(50) NOT NULL,
                `attachable_id` BIGINT UNSIGNED NOT NULL,
                `original_name` VARCHAR(255) NOT NULL,
                `stored_name` VARCHAR(255) NOT NULL,
                `mime_type` VARCHAR(100) NOT NULL,
                `extension` VARCHAR(20) NOT NULL,
                `size_bytes` INT UNSIGNED NOT NULL,
                `disk_path` VARCHAR(500) NOT NULL,
                `checksum_sha256` CHAR(64) NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `attachments_stored_name_unique` (`stored_name`),
                KEY `attachments_attachable_index` (`attachable_type`, `attachable_id`),
                KEY `attachments_uploaded_by_index` (`uploaded_by`),
                CONSTRAINT `attachments_uploaded_by_foreign`
                    FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`)
                    ON DELETE RESTRICT ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS `attachments`');
    }
};
