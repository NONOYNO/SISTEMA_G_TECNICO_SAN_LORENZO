<?php

declare(strict_types=1);

class UserSeeder
{
    public function run(PDO $pdo): void
    {
        $users = [
            [
                'username' => 'admin',
                'email' => 'admin@uesanlorenzo.edu',
                'password' => 'Admin123!',
                'first_name' => 'Admin',
                'last_name' => 'Sistema',
                'role' => 'ADMIN',
            ],
            [
                'username' => 'rector',
                'email' => 'rector@uesanlorenzo.edu',
                'password' => 'Rector123!',
                'first_name' => 'Rector',
                'last_name' => 'Institucional',
                'role' => 'RECTOR',
            ],
            [
                'username' => 'vicerrector',
                'email' => 'vicerrector@uesanlorenzo.edu',
                'password' => 'Vicerrector123!',
                'first_name' => 'Vicerrector',
                'last_name' => 'Académico',
                'role' => 'VICERRECTOR',
            ],
            [
                'username' => 'docente',
                'email' => 'docente@uesanlorenzo.edu',
                'password' => 'Docente123!',
                'first_name' => 'Docente',
                'last_name' => 'Demo',
                'role' => 'DOCENTE',
            ],
        ];

        $findUser = $pdo->prepare('SELECT `id` FROM `users` WHERE `username` = ? OR `email` = ? LIMIT 1');
        $insertUser = $pdo->prepare(
            'INSERT INTO `users`
                (`username`, `email`, `password`, `first_name`, `last_name`, `status`, `created_at`, `updated_at`)
             VALUES (?, ?, ?, ?, ?, \'active\', NOW(), NOW())'
        );
        $updateUser = $pdo->prepare(
            'UPDATE `users`
             SET `email` = ?, `password` = ?, `first_name` = ?, `last_name` = ?, `status` = \'active\', `updated_at` = NOW()
             WHERE `id` = ?'
        );
        $roleStmt = $pdo->prepare('SELECT `id` FROM `roles` WHERE `name` = ? LIMIT 1');
        $linkRole = $pdo->prepare(
            'INSERT INTO `user_roles` (`user_id`, `role_id`, `created_at`)
             VALUES (?, ?, NOW())
             ON DUPLICATE KEY UPDATE `user_id` = VALUES(`user_id`)'
        );

        foreach ($users as $user) {
            $hash = password_hash($user['password'], PASSWORD_DEFAULT);

            $findUser->execute([$user['username'], $user['email']]);
            $userId = $findUser->fetchColumn();

            if ($userId === false) {
                $insertUser->execute([
                    $user['username'],
                    $user['email'],
                    $hash,
                    $user['first_name'],
                    $user['last_name'],
                ]);
                $userId = (int) $pdo->lastInsertId();
            } else {
                $userId = (int) $userId;
                $updateUser->execute([
                    $user['email'],
                    $hash,
                    $user['first_name'],
                    $user['last_name'],
                    $userId,
                ]);
            }

            $roleStmt->execute([$user['role']]);
            $roleId = $roleStmt->fetchColumn();
            if ($roleId === false) {
                throw new RuntimeException('Role not found: ' . $user['role']);
            }

            $linkRole->execute([$userId, (int) $roleId]);
        }
    }
}
