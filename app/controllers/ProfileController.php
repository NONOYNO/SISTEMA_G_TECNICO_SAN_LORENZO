<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\UserRepository;
use App\Services\AuditService;
use RuntimeException;
use Throwable;

final class ProfileController extends Controller
{
    private UserRepository $users;
    private AuditService $audit;

    public function __construct(?UserRepository $users = null, ?AuditService $audit = null)
    {
        $this->users = $users ?? new UserRepository();
        $this->audit = $audit ?? new AuditService();
    }

    public function show(): void
    {
        $authUser = auth_user();
        if ($authUser === null) {
            $this->redirect('/login');
        }

        $user = $this->users->findById((int) $authUser['id']);
        if ($user === null) {
            auth_logout();
            flash('error', 'Usuario no encontrado.');
            $this->redirect('/login');
        }

        unset($user['password']);
        $roles = $this->users->getRoleNames((int) $user['id']);

        $this->view('profile.show', [
            'title' => 'Mi perfil',
            'user' => $user,
            'roles' => $roles,
            'errors' => [],
        ]);
    }

    public function update(): void
    {
        $authUser = auth_user();
        if ($authUser === null) {
            $this->redirect('/login');
        }

        $userId = (int) $authUser['id'];
        $current = $this->users->findById($userId);

        if ($current === null) {
            auth_logout();
            flash('error', 'Usuario no encontrado.');
            $this->redirect('/login');
        }

        $firstName = trim((string) ($_POST['first_name'] ?? ''));
        $lastName = trim((string) ($_POST['last_name'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $currentPassword = (string) ($_POST['current_password'] ?? '');
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $newPasswordConfirmation = (string) ($_POST['new_password_confirmation'] ?? '');

        $errors = [];

        if ($firstName === '') {
            $errors['first_name'] = 'El nombre es obligatorio.';
        }
        if ($lastName === '') {
            $errors['last_name'] = 'El apellido es obligatorio.';
        }
        if ($phone !== '' && strlen($phone) > 30) {
            $errors['phone'] = 'El teléfono no puede superar 30 caracteres.';
        }

        $changePassword = $currentPassword !== '' || $newPassword !== '' || $newPasswordConfirmation !== '';

        if ($changePassword) {
            if ($currentPassword === '' || $newPassword === '') {
                $errors['password'] = 'Para cambiar la contraseña indique la actual y la nueva.';
            } elseif (!password_verify($currentPassword, (string) $current['password'])) {
                $errors['current_password'] = 'La contraseña actual no es correcta.';
            } elseif ($newPassword !== $newPasswordConfirmation) {
                $errors['new_password_confirmation'] = 'La confirmación de la nueva contraseña no coincide.';
            } elseif (strlen($newPassword) < 8
                || !preg_match('/[A-Z]/', $newPassword)
                || !preg_match('/\d/', $newPassword)
            ) {
                $errors['new_password'] = 'La nueva contraseña debe tener mínimo 8 caracteres, una mayúscula y un número.';
            }
        }

        if ($errors !== []) {
            unset($current['password']);
            flash('error', 'Revise los datos del formulario.');
            $this->view('profile.show', [
                'title' => 'Mi perfil',
                'user' => array_merge($current, [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'phone' => $phone,
                ]),
                'roles' => $this->users->getRoleNames($userId),
                'errors' => $errors,
            ]);
            return;
        }

        try {
            $payload = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $phone === '' ? null : $phone,
            ];

            if ($changePassword) {
                $payload['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
            }

            $old = [
                'first_name' => $current['first_name'],
                'last_name' => $current['last_name'],
                'phone' => $current['phone'],
            ];

            $this->users->update($userId, $payload);

            $this->audit->log($userId, 'USER_UPDATED', 'user', $userId, $old, [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $phone === '' ? null : $phone,
                'password_changed' => $changePassword,
            ]);

            $session = auth_user() ?? [];
            $session['first_name'] = $firstName;
            $session['last_name'] = $lastName;
            $session['name'] = trim($firstName . ' ' . $lastName);
            $_SESSION['user'] = $session;

            flash('success', 'Perfil actualizado correctamente.');
            $this->redirect('/profile');
        } catch (Throwable $e) {
            if ($e instanceof RuntimeException || str_contains($e->getMessage(), 'base de datos')) {
                flash('error', 'No se pudo actualizar el perfil.');
                $this->redirect('/profile');
            }

            throw $e;
        }
    }
}
