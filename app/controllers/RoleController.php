<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\RoleService;

final class RoleController extends Controller
{
    private RoleService $roles;

    public function __construct(?RoleService $roles = null)
    {
        $this->roles = $roles ?? new RoleService();
    }

    public function index(): void
    {
        $this->view('roles.index', [
            'title' => 'Roles y permisos',
            'roles' => $this->roles->all(),
        ]);
    }

    public function create(): void
    {
        $this->view('roles.create', [
            'title' => 'Nuevo rol',
            'permissionsGrouped' => $this->roles->permissionsGrouped(),
            'selectedPermissions' => [],
            'errors' => [],
        ]);
    }

    public function store(): void
    {
        $input = $this->inputFromRequest();
        flash_input($input);

        $result = $this->roles->create($input);

        if (!$result['ok']) {
            flash('error', $result['message']);
            $_SESSION['_flash']['errors'] = $result['errors'] ?? [];
            $this->redirect('/roles/create');
        }

        clear_old_input();
        flash('success', 'Rol creado correctamente.');
        $this->redirect('/roles/' . $result['id'] . '/edit');
    }

    public function edit(string $id): void
    {
        $roleId = (int) $id;
        $role = $this->roles->find($roleId);

        if ($role === null) {
            abort(404, 'Rol no encontrado.');
        }

        $this->view('roles.edit', [
            'title' => 'Editar rol',
            'role' => $role,
            'permissionsGrouped' => $this->roles->permissionsGrouped(),
            'selectedPermissions' => $this->roles->getPermissionIds($roleId),
            'errors' => [],
        ]);
    }

    public function update(string $id): void
    {
        $roleId = (int) $id;
        $input = $this->inputFromRequest();
        flash_input($input);

        $result = $this->roles->update($roleId, $input);

        if (!$result['ok']) {
            flash('error', $result['message']);
            $_SESSION['_flash']['errors'] = $result['errors'] ?? [];
            $this->redirect('/roles/' . $roleId . '/edit');
        }

        clear_old_input();
        flash('success', 'Rol actualizado correctamente.');
        $this->redirect('/roles/' . $roleId . '/edit');
    }

    public function destroy(string $id): void
    {
        $roleId = (int) $id;
        $result = $this->roles->delete($roleId);

        if (!$result['ok']) {
            flash('error', $result['message']);
            $this->redirect('/roles');
        }

        flash('success', 'Rol eliminado correctamente.');
        $this->redirect('/roles');
    }

    /**
     * @return array<string, mixed>
     */
    private function inputFromRequest(): array
    {
        $permissionIds = $_POST['permission_ids'] ?? [];
        if (!is_array($permissionIds)) {
            $permissionIds = [];
        }

        return [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'display_name' => trim((string) ($_POST['display_name'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'permission_ids' => array_map('intval', $permissionIds),
        ];
    }
}
