<?php

declare(strict_types=1);

class RolePermissionSeeder
{
    public function run(PDO $pdo): void
    {
        $allPermissionNames = $pdo->query('SELECT `name` FROM `permissions`')->fetchAll(PDO::FETCH_COLUMN);

        $matrix = [
            'ADMIN' => $allPermissionNames,
            'VICERRECTOR' => [
                'announcements.view',
                'announcements.create',
                'announcements.edit',
                'announcements.delete',
                'announcements.publish',
                'notifications.view',
                'notifications.create',
                'files.upload',
                'files.download',
                'files.delete',
                'reports.view',
            ],
            'RECTOR' => [
                'announcements.view',
                'notifications.view',
                'files.download',
                'reports.view',
                'users.view',
            ],
            'DOCENTE' => [
                'announcements.view',
                'notifications.view',
                'files.download',
            ],
        ];

        $roleStmt = $pdo->prepare('SELECT `id` FROM `roles` WHERE `name` = ? LIMIT 1');
        $permStmt = $pdo->prepare('SELECT `id` FROM `permissions` WHERE `name` = ? LIMIT 1');
        $insert = $pdo->prepare(
            'INSERT INTO `role_permissions` (`role_id`, `permission_id`, `created_at`)
             VALUES (?, ?, NOW())
             ON DUPLICATE KEY UPDATE `role_id` = VALUES(`role_id`)'
        );

        foreach ($matrix as $roleName => $permissionNames) {
            $roleStmt->execute([$roleName]);
            $roleId = $roleStmt->fetchColumn();
            if ($roleId === false) {
                throw new RuntimeException("Role not found: {$roleName}");
            }

            foreach ($permissionNames as $permissionName) {
                $permStmt->execute([$permissionName]);
                $permissionId = $permStmt->fetchColumn();
                if ($permissionId === false) {
                    throw new RuntimeException("Permission not found: {$permissionName}");
                }
                $insert->execute([(int) $roleId, (int) $permissionId]);
            }
        }
    }
}
