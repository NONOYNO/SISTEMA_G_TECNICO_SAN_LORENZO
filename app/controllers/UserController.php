<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\RoleRepository;
use App\Services\UserService;

final class UserController extends Controller
{
    private UserService $users;
    private RoleRepository $roles;

    public function __construct(?UserService $users = null, ?RoleRepository $roles = null)
    {
        $this->users = $users ?? new UserService();
        $this->roles = $roles ?? new RoleRepository();
    }

    public function index(): void
    {
        $q = trim((string) ($_GET['q'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));
        $role = trim((string) ($_GET['role'] ?? ''));
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $result = $this->users->paginate([
            'q' => $q,
            'status' => $status,
            'role' => $role,
        ], $page, 15);

        $this->view('users.index', [
            'title' => 'Usuarios',
            'users' => $result['items'],
            'pagination' => $result,
            'roles' => $this->roles->all(),
            'filters' => [
                'q' => $q,
                'status' => $status,
                'role' => $role,
            ],
        ]);
    }

    public function create(): void
    {
        $this->view('users.create', [
            'title' => 'Nuevo usuario',
            'roles' => $this->roles->all(),
            'errors' => [],
        ]);
    }

    public function store(): void
    {
        $input = $this->inputFromRequest();
        $this->flashSafeInput($input);

        $result = $this->users->create($input);

        if (!$result['ok']) {
            flash('error', $result['message']);
            $_SESSION['_flash']['errors'] = $result['errors'] ?? [];
            $this->redirect('/users/create');
        }

        clear_old_input();
        flash('success', 'Usuario creado correctamente.');
        $this->redirect('/users/' . $result['id'] . '/edit');
    }

    public function edit(string $id): void
    {
        $userId = (int) $id;
        $bundle = $this->users->findWithRoles($userId);

        if ($bundle === null) {
            abort(404, 'Usuario no encontrado.');
        }

        $this->view('users.edit', [
            'title' => 'Editar usuario',
            'user' => $bundle['user'],
            'roleIds' => $bundle['role_ids'],
            'roles' => $this->roles->all(),
            'errors' => [],
        ]);
    }

    public function update(string $id): void
    {
        $userId = (int) $id;
        $input = $this->inputFromRequest();
        $this->flashSafeInput($input);

        $result = $this->users->update($userId, $input);

        if (!$result['ok']) {
            flash('error', $result['message']);
            $_SESSION['_flash']['errors'] = $result['errors'] ?? [];
            $this->redirect('/users/' . $userId . '/edit');
        }

        clear_old_input();
        flash('success', 'Usuario actualizado correctamente.');
        $this->redirect('/users/' . $userId . '/edit');
    }

    public function destroy(string $id): void
    {
        $userId = (int) $id;
        $result = $this->users->deactivate($userId);

        if (!$result['ok']) {
            flash('error', $result['message']);
            $this->redirect('/users');
        }

        flash('success', 'Usuario desactivado correctamente.');
        $this->redirect('/users');
    }

    /**
     * @return array<string, mixed>
     */
    private function inputFromRequest(): array
    {
        $roleIds = $_POST['role_ids'] ?? [];
        if (!is_array($roleIds)) {
            $roleIds = [];
        }

        return [
            'username' => trim((string) ($_POST['username'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'first_name' => trim((string) ($_POST['first_name'] ?? '')),
            'last_name' => trim((string) ($_POST['last_name'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'status' => trim((string) ($_POST['status'] ?? 'pending')),
            'password' => (string) ($_POST['password'] ?? ''),
            'role_ids' => array_map('intval', $roleIds),
        ];
    }

    /**
     * @param array<string, mixed> $input
     */
    private function flashSafeInput(array $input): void
    {
        unset($input['password']);
        flash_input($input);
    }
}
