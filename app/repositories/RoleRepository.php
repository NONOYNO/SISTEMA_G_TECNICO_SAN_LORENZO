<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Helpers\Database;
use PDO;
use RuntimeException;

final class RoleRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function all(): array
    {
        $stmt = $this->pdo->query(
            'SELECT r.id, r.name, r.display_name, r.description, r.is_system, r.created_at, r.updated_at,
                    (SELECT COUNT(*) FROM user_roles ur WHERE ur.role_id = r.id) AS users_count,
                    (SELECT COUNT(*) FROM role_permissions rp WHERE rp.role_id = r.id) AS permissions_count
             FROM roles r
             ORDER BY r.is_system DESC, r.name ASC'
        );

        /** @var list<array<string, mixed>> $rows */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $rows;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, display_name, description, is_system, created_at, updated_at
             FROM roles
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    public function findByName(string $name): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, display_name, description, is_system, created_at, updated_at
             FROM roles
             WHERE name = :name
             LIMIT 1'
        );
        $stmt->execute(['name' => $name]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    /**
     * @param array{name:string,display_name:string,description?:?string,is_system?:int|bool} $data
     */
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO roles (name, display_name, description, is_system, created_at, updated_at)
             VALUES (:name, :display_name, :description, :is_system, NOW(), NOW())'
        );

        $stmt->execute([
            'name' => $data['name'],
            'display_name' => $data['display_name'],
            'description' => $data['description'] ?? null,
            'is_system' => !empty($data['is_system']) ? 1 : 0,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): bool
    {
        $role = $this->find($id);
        if ($role === null) {
            return false;
        }

        $allowed = ['display_name', 'description'];
        if ((int) ($role['is_system'] ?? 0) !== 1) {
            $allowed[] = 'name';
        }

        $sets = [];
        $params = ['id' => $id];

        foreach ($allowed as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }
            $sets[] = "`{$field}` = :{$field}";
            $params[$field] = $data[$field];
        }

        if ($sets === []) {
            return false;
        }

        $sets[] = '`updated_at` = NOW()';
        $sql = 'UPDATE roles SET ' . implode(', ', $sets) . ' WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $role = $this->find($id);
        if ($role === null) {
            return false;
        }

        if ((int) ($role['is_system'] ?? 0) === 1) {
            throw new RuntimeException('No se puede eliminar un rol del sistema.');
        }

        $countStmt = $this->pdo->prepare('SELECT COUNT(*) FROM user_roles WHERE role_id = :id');
        $countStmt->execute(['id' => $id]);
        if ((int) $countStmt->fetchColumn() > 0) {
            throw new RuntimeException('No se puede eliminar un rol asignado a usuarios.');
        }

        $stmt = $this->pdo->prepare('DELETE FROM roles WHERE id = :id AND is_system = 0');

        return $stmt->execute(['id' => $id]);
    }

    /**
     * @return list<int>
     */
    public function getPermissions(int $roleId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT permission_id
             FROM role_permissions
             WHERE role_id = :role_id'
        );
        $stmt->execute(['role_id' => $roleId]);

        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    /**
     * @param list<int> $permissionIds
     */
    public function syncPermissions(int $roleId, array $permissionIds): void
    {
        $permissionIds = array_values(array_unique(array_filter(
            array_map('intval', $permissionIds),
            static fn (int $id): bool => $id > 0
        )));

        $this->pdo->beginTransaction();

        try {
            $delete = $this->pdo->prepare('DELETE FROM role_permissions WHERE role_id = :role_id');
            $delete->execute(['role_id' => $roleId]);

            if ($permissionIds !== []) {
                $insert = $this->pdo->prepare(
                    'INSERT INTO role_permissions (role_id, permission_id, created_at)
                     VALUES (:role_id, :permission_id, NOW())'
                );

                foreach ($permissionIds as $permissionId) {
                    $insert->execute([
                        'role_id' => $roleId,
                        'permission_id' => $permissionId,
                    ]);
                }
            }

            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listPermissionsAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT id, name, group_name, description
             FROM permissions
             ORDER BY group_name ASC, name ASC'
        );

        /** @var list<array<string, mixed>> $rows */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $rows;
    }

    /**
     * @param list<int> $ids
     * @return list<int>
     */
    public function filterExistingIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(
            array_map('intval', $ids),
            static fn (int $id): bool => $id > 0
        )));

        if ($ids === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->pdo->prepare("SELECT id FROM roles WHERE id IN ({$placeholders})");
        $stmt->execute($ids);

        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    /**
     * @param list<int> $ids
     * @return list<int>
     */
    public function filterExistingPermissionIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(
            array_map('intval', $ids),
            static fn (int $id): bool => $id > 0
        )));

        if ($ids === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->pdo->prepare("SELECT id FROM permissions WHERE id IN ({$placeholders})");
        $stmt->execute($ids);

        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }
}
