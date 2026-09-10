<?php

declare(strict_types=1);

class RoleSeeder
{
    public function run(PDO $pdo): void
    {
        $roles = [
            ['ADMIN', 'Administrador', 'Acceso total al portal institucional', 1],
            ['RECTOR', 'Rector', 'Consulta gerencial y reportes', 1],
            ['VICERRECTOR', 'Vicerrector', 'Gestión de avisos, notificaciones y archivos', 1],
            ['DOCENTE', 'Docente', 'Consulta de avisos y notificaciones', 1],
        ];

        $stmt = $pdo->prepare(
            'INSERT INTO `roles` (`name`, `display_name`, `description`, `is_system`, `created_at`, `updated_at`)
             VALUES (?, ?, ?, ?, NOW(), NOW())
             ON DUPLICATE KEY UPDATE
                `display_name` = VALUES(`display_name`),
                `description` = VALUES(`description`),
                `is_system` = VALUES(`is_system`),
                `updated_at` = NOW()'
        );

        foreach ($roles as $role) {
            $stmt->execute($role);
        }
    }
}
