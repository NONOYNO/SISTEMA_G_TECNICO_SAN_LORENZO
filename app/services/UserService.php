<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use RuntimeException;

final class UserService
{
    private UserRepository $users;
    private RoleRepository $roles;
    private AuditService $audit;

    public function __construct(
        ?UserRepository $users = null,
        ?RoleRepository $roles = null,
        ?AuditService $audit = null
    ) {
        $this->users = $users ?? new UserRepository();
        $this->roles = $roles ?? new RoleRepository();
        $this->audit = $audit ?? new AuditService();
    }

    /**
     * @param array{q?:string,status?:string,role?:string|int} $filters
     * @return array{items:list<array<string,mixed>>,total:int,page:int,per_page:int,pages:int}
     */
    public function paginate(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        return $this->users->paginate($filters, $page, $perPage);
    }

    public function findWithRoles(int $id): ?array
    {
        return $this->users->findWithRoles($id);
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
        $roleIds = $payload['role_ids'];
        unset($payload['role_ids']);

        $payload['password'] = password_hash((string) $payload['password'], PASSWORD_DEFAULT);

        $id = $this->users->create($payload);

        if ($roleIds !== []) {
            $this->users->syncRoles($id, $roleIds);
        }

        $this->audit->log(auth_id(), 'USER_CREATED', 'user', $id, null, [
            'username' => $payload['username'],
            'email' => $payload['email'],
            'status' => $payload['status'],
            'role_ids' => $roleIds,
        ]);

        return ['ok' => true, 'id' => $id];
    }

    /**
     * @param array<string, mixed> $data
     * @return array{ok:true,id:int}|array{ok:false,message:string,errors:array<string,string>}
     */
    public function update(int $id, array $data): array
    {
        $existing = $this->users->find($id);
        if ($existing === null) {
            return [
                'ok' => false,
                'message' => 'Usuario no encontrado.',
                'errors' => ['id' => 'Usuario no encontrado.'],
            ];
        }

        $validated = $this->validate($data, $id);
        if (!$validated['ok']) {
            return $validated;
        }

        $payload = $validated['data'];
        $roleIds = $payload['role_ids'];
        unset($payload['role_ids']);

        if (isset($payload['password']) && $payload['password'] !== '') {
            $payload['password'] = password_hash((string) $payload['password'], PASSWORD_DEFAULT);
        } else {
            unset($payload['password']);
        }

        if (
            isset($payload['status'])
            && $payload['status'] === 'inactive'
            && (string) $existing['status'] === 'active'
            && $this->users->isLastActiveAdmin($id)
        ) {
            return [
                'ok' => false,
                'message' => 'No se puede desactivar al último administrador activo.',
                'errors' => ['status' => 'No se puede desactivar al último administrador activo.'],
            ];
        }

        $this->users->update($id, $payload);

        try {
            $this->users->syncRoles($id, $roleIds);
        } catch (RuntimeException $e) {
            return [
                'ok' => false,
                'message' => $e->getMessage(),
                'errors' => ['role_ids' => $e->getMessage()],
            ];
        }

        $safeNew = $payload;
        unset($safeNew['password']);

        $this->audit->log(auth_id(), 'USER_UPDATED', 'user', $id, [
            'username' => $existing['username'],
            'email' => $existing['email'],
            'status' => $existing['status'],
        ], array_merge($safeNew, ['role_ids' => $roleIds]));

        return ['ok' => true, 'id' => $id];
    }

    /**
     * Soft deactivate (preferred destroy).
     *
     * @return array{ok:true}|array{ok:false,message:string}
     */
    public function deactivate(int $id): array
    {
        $existing = $this->users->find($id);
        if ($existing === null) {
            return ['ok' => false, 'message' => 'Usuario no encontrado.'];
        }

        try {
            $this->users->deactivate($id);
        } catch (RuntimeException $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }

        $this->audit->log(auth_id(), 'USER_STATUS_CHANGED', 'user', $id, [
            'status' => $existing['status'],
        ], [
            'status' => 'inactive',
        ]);

        return ['ok' => true];
    }

    /**
     * @param list<int> $roleIds
     * @return array{ok:true}|array{ok:false,message:string}
     */
    public function assignRoles(int $userId, array $roleIds): array
    {
        if ($this->users->find($userId) === null) {
            return ['ok' => false, 'message' => 'Usuario no encontrado.'];
        }

        $roleIds = $this->roles->filterExistingIds($roleIds);

        try {
            $oldRoles = $this->users->getRoleNames($userId);
            $this->users->syncRoles($userId, $roleIds);
            $newRoles = $this->users->getRoleNames($userId);
        } catch (RuntimeException $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }

        $this->audit->log(auth_id(), 'ROLE_ASSIGNED', 'user', $userId, [
            'roles' => $oldRoles,
        ], [
            'roles' => $newRoles,
            'role_ids' => $roleIds,
        ]);

        return ['ok' => true];
    }

    /**
     * @param array<string, mixed> $data
     * @return array{ok:true,data:array<string,mixed>}|array{ok:false,message:string,errors:array<string,string>}
     */
    private function validate(array $data, ?int $exceptId): array
    {
        $errors = [];

        $username = trim((string) ($data['username'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $firstName = trim((string) ($data['first_name'] ?? ''));
        $lastName = trim((string) ($data['last_name'] ?? ''));
        $phone = trim((string) ($data['phone'] ?? ''));
        $status = trim((string) ($data['status'] ?? 'pending'));
        $password = (string) ($data['password'] ?? '');
        $roleIdsRaw = $data['role_ids'] ?? [];

        if ($username === '' || !preg_match('/^[A-Za-z0-9._-]{3,50}$/', $username)) {
            $errors['username'] = 'Usuario inválido (3-50: letras, números, . _ -).';
        } elseif ($this->users->findByUsername($username, $exceptId) !== null) {
            $errors['username'] = 'El nombre de usuario ya está en uso.';
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
            $errors['email'] = 'Ingrese un correo válido.';
        } elseif ($this->users->findByEmail($email, $exceptId) !== null) {
            $errors['email'] = 'El correo ya está registrado.';
        }

        if ($firstName === '' || strlen($firstName) > 100) {
            $errors['first_name'] = 'El nombre es obligatorio (máx. 100).';
        }

        if ($lastName === '' || strlen($lastName) > 100) {
            $errors['last_name'] = 'El apellido es obligatorio (máx. 100).';
        }

        if ($phone !== '' && strlen($phone) > 30) {
            $errors['phone'] = 'Teléfono demasiado largo (máx. 30).';
        }

        if (!in_array($status, ['active', 'inactive', 'pending'], true)) {
            $errors['status'] = 'Estado inválido.';
        }

        if ($exceptId === null) {
            if (strlen($password) < 8) {
                $errors['password'] = 'La contraseña debe tener al menos 8 caracteres.';
            }
        } elseif ($password !== '' && strlen($password) < 8) {
            $errors['password'] = 'La contraseña debe tener al menos 8 caracteres.';
        }

        if (!is_array($roleIdsRaw)) {
            $roleIdsRaw = [];
        }

        $roleIds = $this->roles->filterExistingIds(array_map('intval', $roleIdsRaw));

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
                'username' => $username,
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $phone !== '' ? $phone : null,
                'status' => $status,
                'password' => $password,
                'role_ids' => $roleIds,
            ],
        ];
    }
}
