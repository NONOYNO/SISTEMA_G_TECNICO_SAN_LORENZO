<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\RoleRepository;
use RuntimeException;

final class RoleService
{
    private RoleRepository $roles;
    private AuditService $audit;

    public function __construct(?RoleRepository $roles = null, ?AuditService $audit = null)
    {
        $this->roles = $roles ?? new RoleRepository();
        $this->audit = $audit ?? new AuditService();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function all(): array
    {
        return $this->roles->all();
    }

    public function find(int $id): ?array
    {
        return $this->roles->find($id);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listPermissionsAll(): array
    {
        return $this->roles->listPermissionsAll();
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    public function permissionsGrouped(): array
    {
        $grouped = [];
        foreach ($this->roles->listPermissionsAll() as $permission) {
            $group = (string) ($permission['group_name'] ?? 'otros');
            $grouped[$group][] = $permission;
        }

        return $grouped;
    }

    /**
     * @return list<int>
     */
    public function getPermissionIds(int $roleId): array
    {
        return $this->roles->getPermissions($roleId);
    }

    /**
     * @param array<string, mixed> $data
     * @return array{ok:true,id:int}|array{ok:false,message:string,errors:array<string,string>}
     */
    public function create(array $data): array
    {
        $validated = $this->validate($data, null);
        if (!$validated['ok']) {
            return $validated;
        }

        $payload = $validated['data'];
        $permissionIds = $payload['permission_ids'];
        unset($payload['permission_ids']);

        $id = $this->roles->create($payload);
        $this->roles->syncPermissions($id, $permissionIds);

        $this->audit->log(auth_id(), 'ROLE_CREATED', 'role', $id, null, [
            'name' => $payload['name'],
            'permission_ids' => $permissionIds,
        ]);

        return ['ok' => true, 'id' => $id];
    }

    /**
     * @param array<string, mixed> $data
     * @return array{ok:true,id:int}|array{ok:false,message:string,errors:array<string,string>}
     */
    public function update(int $id, array $data): array
    {
        $existing = $this->roles->find($id);
        if ($existing === null) {
            return [
                'ok' => false,
                'message' => 'Rol no encontrado.',
                'errors' => ['id' => 'Rol no encontrado.'],
            ];
        }

        $validated = $this->validate($data, $id, (int) ($existing['is_system'] ?? 0) === 1);
        if (!$validated['ok']) {
            return $validated;
        }

        $payload = $validated['data'];
        $permissionIds = $payload['permission_ids'];
        unset($payload['permission_ids'], $payload['is_system']);

        if ((int) ($existing['is_system'] ?? 0) === 1) {
            unset($payload['name']);
        }

        $oldPermissions = $this->roles->getPermissions($id);
        $this->roles->update($id, $payload);
        $this->roles->syncPermissions($id, $permissionIds);

        $this->audit->log(auth_id(), 'ROLE_PERMISSIONS_UPDATED', 'role', $id, [
            'display_name' => $existing['display_name'],
            'permission_ids' => $oldPermissions,
        ], [
            'display_name' => $payload['display_name'] ?? $existing['display_name'],
            'permission_ids' => $permissionIds,
        ]);

        return ['ok' => true, 'id' => $id];
    }

    /**
     * @return array{ok:true}|array{ok:false,message:string}
     */
    public function delete(int $id): array
    {
        $existing = $this->roles->find($id);
        if ($existing === null) {
            return ['ok' => false, 'message' => 'Rol no encontrado.'];
        }

        try {
            $this->roles->delete($id);
        } catch (RuntimeException $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }

        $this->audit->log(auth_id(), 'ROLE_DELETED', 'role', $id, [
            'name' => $existing['name'],
            'display_name' => $existing['display_name'],
        ], null);

        return ['ok' => true];
    }

    /**
     * @param array<string, mixed> $data
     * @return array{ok:true,data:array<string,mixed>}|array{ok:false,message:string,errors:array<string,string>}
     */
    private function validate(array $data, ?int $exceptId, bool $isSystem = false): array
    {
        $errors = [];

        $name = strtoupper(trim((string) ($data['name'] ?? '')));
        $displayName = trim((string) ($data['display_name'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));
        $permissionIdsRaw = $data['permission_ids'] ?? [];

        if (!$isSystem || $exceptId === null) {
            if ($name === '' || !preg_match('/^[A-Z][A-Z0-9_]{1,49}$/', $name)) {
                $errors['name'] = 'Nombre de rol inválido (MAYÚSCULAS, números y _).';
            } else {
                $other = $this->roles->findByName($name);
                if ($other !== null && ($exceptId === null || (int) $other['id'] !== $exceptId)) {
                    $errors['name'] = 'Ya existe un rol con ese nombre.';
                }
            }
        }

        if ($displayName === '' || strlen($displayName) > 100) {
            $errors['display_name'] = 'El nombre visible es obligatorio (máx. 100).';
        }

        if (strlen($description) > 255) {
            $errors['description'] = 'La descripción es demasiado larga (máx. 255).';
        }

        if (!is_array($permissionIdsRaw)) {
            $permissionIdsRaw = [];
        }

        $permissionIds = $this->roles->filterExistingPermissionIds(array_map('intval', $permissionIdsRaw));

        if ($errors !== []) {
            return [
                'ok' => false,
                'message' => 'Revise los datos del formulario.',
                'errors' => $errors,
            ];
        }

        return [
            'ok' => true,
            'data' => [
                'name' => $name,
                'display_name' => $displayName,
                'description' => $description !== '' ? $description : null,
                'is_system' => 0,
                'permission_ids' => $permissionIds,
            ],
        ];
    }
}
