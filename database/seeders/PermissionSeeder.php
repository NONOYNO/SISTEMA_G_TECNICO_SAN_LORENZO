<?php

declare(strict_types=1);

class PermissionSeeder
{
    public function run(PDO $pdo): void
    {
        $permissions = [
            ['users.view', 'users', 'Ver usuarios'],
            ['users.create', 'users', 'Crear usuarios'],
            ['users.edit', 'users', 'Editar usuarios'],
            ['users.delete', 'users', 'Eliminar usuarios'],
            ['roles.view', 'roles', 'Ver roles'],
            ['roles.create', 'roles', 'Crear roles'],
            ['roles.edit', 'roles', 'Editar roles'],
            ['roles.delete', 'roles', 'Eliminar roles'],
            ['announcements.view', 'announcements', 'Ver avisos'],
            ['announcements.create', 'announcements', 'Crear avisos'],
            ['announcements.edit', 'announcements', 'Editar avisos'],
            ['announcements.delete', 'announcements', 'Eliminar avisos'],
            ['announcements.publish', 'announcements', 'Publicar/archivar avisos'],
            ['notifications.view', 'notifications', 'Ver notificaciones'],
            ['notifications.create', 'notifications', 'Crear notificaciones'],
            ['files.upload', 'files', 'Subir archivos'],
            ['files.download', 'files', 'Descargar archivos'],
            ['files.delete', 'files', 'Eliminar archivos'],
            ['reports.view', 'reports', 'Ver reportes'],
            ['audit.view', 'audit', 'Ver auditoría'],
        ];

        $stmt = $pdo->prepare(
            'INSERT INTO `permissions` (`name`, `group_name`, `description`, `created_at`, `updated_at`)
             VALUES (?, ?, ?, NOW(), NOW())
             ON DUPLICATE KEY UPDATE
                `group_name` = VALUES(`group_name`),
                `description` = VALUES(`description`),
                `updated_at` = NOW()'
        );

        foreach ($permissions as $permission) {
            $stmt->execute($permission);
        }
    }
}
