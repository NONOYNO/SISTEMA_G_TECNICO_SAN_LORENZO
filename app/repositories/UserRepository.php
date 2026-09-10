<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Helpers\Database;
use PDO;
use RuntimeException;

final class UserRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, username, email, password, first_name, last_name, phone, status,
                    last_login_at, failed_login_attempts, locked_until, created_at, updated_at,
                    TRIM(CONCAT(COALESCE(first_name, \'\'), \' \', COALESCE(last_name, \'\'))) AS name
             FROM users
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    public function find(int $id): ?array
    {
        $user = $this->findById($id);
        if ($user === null) {
            return null;
        }

        unset($user['password']);

        return $user;
    }

    /**
     * @return array{user:array<string,mixed>,roles:list<array<string,mixed>>,role_ids:list<int>}|null
     */
    public function findWithRoles(int $id): ?array
    {
        $user = $this->find($id);
        if ($user === null) {
            return null;
        }

        $stmt = $this->pdo->prepare(
            'SELECT r.id, r.name, r.display_name
             FROM roles r
             INNER JOIN user_roles ur ON ur.role_id = r.id
             WHERE ur.user_id = :user_id
             ORDER BY r.name'
        );
        $stmt->execute(['user_id' => $id]);

        /** @var list<array<string, mixed>> $roles */
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $roleIds = array_map(static fn (array $r): int => (int) $r['id'], $roles);

        return [
            'user' => $user,
            'roles' => $roles,
            'role_ids' => $roleIds,
        ];
    }

    public function findByLogin(string $login): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, username, email, password, first_name, last_name, phone, status,
                    last_login_at, failed_login_attempts, locked_until, created_at, updated_at,
                    TRIM(CONCAT(COALESCE(first_name, \'\'), \' \', COALESCE(last_name, \'\'))) AS name
             FROM users
             WHERE email = :email OR username = :username
             LIMIT 1'
        );
        $stmt->execute(['email' => $login, 'username' => $login]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    public function findByEmail(string $email, ?int $exceptId = null): ?array
    {
        $sql = 'SELECT id, username, email, first_name, last_name, status
                FROM users
                WHERE email = :email';
        $params = ['email' => $email];

        if ($exceptId !== null) {
            $sql .= ' AND id <> :except_id';
            $params['except_id'] = $exceptId;
        }

        $sql .= ' LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    public function findByUsername(string $username, ?int $exceptId = null): ?array
    {
        $sql = 'SELECT id, username, email, first_name, last_name, status
                FROM users
                WHERE username = :username';
        $params = ['username' => $username];

        if ($exceptId !== null) {
            $sql .= ' AND id <> :except_id';
            $params['except_id'] = $exceptId;
        }

        $sql .= ' LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    /**
     * @param array{q?:string,status?:string,role?:string|int} $filters
     * @return array{items:list<array<string,mixed>>,total:int,page:int,per_page:int,pages:int}
     */
    public function paginate(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $where = [];
        $params = [];

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $where[] = '(u.username LIKE :q OR u.email LIKE :q OR u.first_name LIKE :q OR u.last_name LIKE :q
                        OR CONCAT(u.first_name, \' \', u.last_name) LIKE :q)';
            $params['q'] = '%' . $q . '%';
        }

        $status = trim((string) ($filters['status'] ?? ''));
        if ($status !== '' && in_array($status, ['active', 'inactive', 'pending'], true)) {
            $where[] = 'u.status = :status';
            $params['status'] = $status;
        }

        $role = $filters['role'] ?? '';
        if ($role !== '' && $role !== null) {
            if (is_numeric($role)) {
                $where[] = 'EXISTS (
                    SELECT 1 FROM user_roles urf
                    WHERE urf.user_id = u.id AND urf.role_id = :role_id
                )';
                $params['role_id'] = (int) $role;
            } else {
                $where[] = 'EXISTS (
                    SELECT 1 FROM user_roles urf
                    INNER JOIN roles rf ON rf.id = urf.role_id
                    WHERE urf.user_id = u.id AND rf.name = :role_name
                )';
                $params['role_name'] = (string) $role;
            }
        }

        $whereSql = $where === [] ? '1=1' : implode(' AND ', $where);

        $countSql = "SELECT COUNT(*) FROM users u WHERE {$whereSql}";
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $sql = "SELECT u.id, u.username, u.email, u.first_name, u.last_name, u.phone, u.status,
                       u.created_at, u.updated_at, u.last_login_at,
                       TRIM(CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, ''))) AS name
                FROM users u
                WHERE {$whereSql}
                ORDER BY u.last_name ASC, u.first_name ASC, u.id ASC
                LIMIT {$perPage} OFFSET {$offset}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        /** @var list<array<string, mixed>> $items */
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($items as &$item) {
            $item['roles'] = $this->getRolesForUser((int) $item['id']);
        }
        unset($item);

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'pages' => $total > 0 ? (int) ceil($total / $perPage) : 1,
        ];
    }

    /**
     * @return list<array{id:int,name:string,display_name:string}>
     */
    public function getRolesForUser(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT r.id, r.name, r.display_name
             FROM roles r
             INNER JOIN user_roles ur ON ur.role_id = r.id
             WHERE ur.user_id = :user_id
             ORDER BY r.name'
        );
        $stmt->execute(['user_id' => $userId]);

        /** @var list<array{id:int|string,name:string,display_name:string}> $rows */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(static function (array $row): array {
            return [
                'id' => (int) $row['id'],
                'name' => (string) $row['name'],
                'display_name' => (string) $row['display_name'],
            ];
        }, $rows);
    }

    /**
     * @param array{
     *   username:string,
     *   email:string,
     *   password:string,
     *   first_name:string,
     *   last_name:string,
     *   phone?:?string,
     *   status?:string
     * } $data
     */
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users
                (username, email, password, first_name, last_name, phone, status, created_at, updated_at)
             VALUES
                (:username, :email, :password, :first_name, :last_name, :phone, :status, NOW(), NOW())'
        );

        $stmt->execute([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'] ?? null,
            'status' => $data['status'] ?? 'pending',
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): bool
    {
        $allowed = ['first_name', 'last_name', 'phone', 'email', 'username', 'password', 'status'];
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
        $sql = 'UPDATE users SET ' . implode(', ', $sets) . ' WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute($params);
    }

    public function deactivate(int $id): bool
    {
        if ($this->isLastActiveAdmin($id)) {
            throw new RuntimeException('No se puede desactivar al último administrador activo.');
        }

        return $this->update($id, ['status' => 'inactive']);
    }

    public function delete(int $id): bool
    {
        if ($this->isLastActiveAdmin($id)) {
            throw new RuntimeException('No se puede eliminar al último administrador activo.');
        }

        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = :id');

        return $stmt->execute(['id' => $id]);
    }

    /**
     * @param list<int> $roleIds
     */
    public function syncRoles(int $userId, array $roleIds): void
    {
        $roleIds = array_values(array_unique(array_filter(
            array_map('intval', $roleIds),
            static fn (int $id): bool => $id > 0
        )));

        $hadAdmin = $this->userHasAdminRole($userId);
        $willHaveAdmin = $this->roleIdsIncludeAdmin($roleIds);

        if ($hadAdmin && !$willHaveAdmin && $this->countActiveAdmins() <= 1) {
            throw new RuntimeException('No se puede quitar el rol ADMIN al último administrador activo.');
        }

        $this->pdo->beginTransaction();

        try {
            $delete = $this->pdo->prepare('DELETE FROM user_roles WHERE user_id = :user_id');
            $delete->execute(['user_id' => $userId]);

            if ($roleIds !== []) {
                $insert = $this->pdo->prepare(
                    'INSERT INTO user_roles (user_id, role_id, created_at)
                     VALUES (:user_id, :role_id, NOW())'
                );

                foreach ($roleIds as $roleId) {
                    $insert->execute([
                        'user_id' => $userId,
                        'role_id' => $roleId,
                    ]);
                }
            }

            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function updateLoginState(
        int $id,
        int $failedAttempts,
        ?string $lockedUntil,
        ?string $lastLoginAt = null
    ): void {
        $stmt = $this->pdo->prepare(
            'UPDATE users
             SET failed_login_attempts = :attempts,
                 locked_until = :locked_until,
                 last_login_at = COALESCE(:last_login_at, last_login_at),
                 updated_at = NOW()
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
            'attempts' => $failedAttempts,
            'locked_until' => $lockedUntil,
            'last_login_at' => $lastLoginAt,
        ]);
    }

    /**
     * @return list<string>
     */
    public function getRoleNames(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT r.name
             FROM roles r
             INNER JOIN user_roles ur ON ur.role_id = r.id
             WHERE ur.user_id = :user_id
             ORDER BY r.name'
        );
        $stmt->execute(['user_id' => $userId]);

        return array_values(array_filter(array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN))));
    }

    /**
     * @return list<string>
     */
    public function getPermissionNames(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT DISTINCT p.name
             FROM permissions p
             INNER JOIN role_permissions rp ON rp.permission_id = p.id
             INNER JOIN user_roles ur ON ur.role_id = rp.role_id
             WHERE ur.user_id = :user_id
             ORDER BY p.name'
        );
        $stmt->execute(['user_id' => $userId]);

        return array_values(array_filter(array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN))));
    }

    public function countActiveAdmins(): int
    {
        $stmt = $this->pdo->query(
            "SELECT COUNT(DISTINCT u.id)
             FROM users u
             INNER JOIN user_roles ur ON ur.user_id = u.id
             INNER JOIN roles r ON r.id = ur.role_id
             WHERE r.name = 'ADMIN' AND u.status = 'active'"
        );

        return (int) $stmt->fetchColumn();
    }

    public function isLastActiveAdmin(int $userId): bool
    {
        if ($this->countActiveAdmins() > 1) {
            return false;
        }

        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*)
             FROM users u
             INNER JOIN user_roles ur ON ur.user_id = u.id
             INNER JOIN roles r ON r.id = ur.role_id
             WHERE u.id = :id AND r.name = 'ADMIN' AND u.status = 'active'"
        );
        $stmt->execute(['id' => $userId]);

        return (int) $stmt->fetchColumn() > 0;
    }

    private function userHasAdminRole(int $userId): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*)
             FROM user_roles ur
             INNER JOIN roles r ON r.id = ur.role_id
             WHERE ur.user_id = :user_id AND r.name = 'ADMIN'"
        );
        $stmt->execute(['user_id' => $userId]);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * @param list<int> $roleIds
     */
    private function roleIdsIncludeAdmin(array $roleIds): bool
    {
        if ($roleIds === []) {
            return false;
        }

        $placeholders = implode(',', array_fill(0, count($roleIds), '?'));
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM roles WHERE name = 'ADMIN' AND id IN ({$placeholders})"
        );
        $stmt->execute($roleIds);

        return (int) $stmt->fetchColumn() > 0;
    }
}
