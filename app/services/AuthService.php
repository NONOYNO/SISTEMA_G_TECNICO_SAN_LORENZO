<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;

final class AuthService
{
    private const MAX_ATTEMPTS = 5;
    private const LOCK_MINUTES = 15;

    private UserRepository $users;
    private AuditService $audit;

    public function __construct(?UserRepository $users = null, ?AuditService $audit = null)
    {
        $this->users = $users ?? new UserRepository();
        $this->audit = $audit ?? new AuditService();
    }

    /**
     * @return array{
     *   id:int,
     *   username:string,
     *   email:string,
     *   name:string,
     *   first_name:string,
     *   last_name:string,
     *   roles:list<string>,
     *   permissions:list<string>
     * }|false
     */
    public function attempt(string $login, string $password): array|false
    {
        $login = trim($login);

        if ($login === '' || $password === '') {
            return false;
        }

        $user = $this->users->findByLogin($login);

        if ($user === null) {
            $this->audit->log(null, 'LOGIN_FAILED', 'auth', null, null, ['login' => $login]);
            return false;
        }

        $userId = (int) $user['id'];

        if (!empty($user['locked_until'])) {
            $lockedUntil = strtotime((string) $user['locked_until']);
            if ($lockedUntil !== false && $lockedUntil > time()) {
                $this->audit->log($userId, 'LOGIN_FAILED', 'auth', $userId, null, [
                    'reason' => 'locked',
                    'login' => $login,
                ]);
                return false;
            }
        }

        if ((string) $user['status'] !== 'active') {
            $this->audit->log($userId, 'LOGIN_FAILED', 'auth', $userId, null, [
                'reason' => 'inactive_or_pending',
                'status' => $user['status'],
                'login' => $login,
            ]);
            return false;
        }

        if (!password_verify($password, (string) $user['password'])) {
            $attempts = (int) $user['failed_login_attempts'] + 1;
            $lockedUntil = null;

            if ($attempts >= self::MAX_ATTEMPTS) {
                $lockedUntil = date('Y-m-d H:i:s', time() + self::LOCK_MINUTES * 60);
                $attempts = self::MAX_ATTEMPTS;
            }

            $this->users->updateLoginState($userId, $attempts, $lockedUntil);
            $this->audit->log($userId, 'LOGIN_FAILED', 'auth', $userId, null, [
                'reason' => 'bad_password',
                'attempts' => $attempts,
                'login' => $login,
            ]);

            return false;
        }

        $this->users->updateLoginState(
            $userId,
            0,
            null,
            date('Y-m-d H:i:s')
        );

        $roles = $this->users->getRoleNames($userId);
        $permissions = $this->users->getPermissionNames($userId);
        $fullName = trim((string) ($user['first_name'] ?? '') . ' ' . (string) ($user['last_name'] ?? ''));

        $sessionUser = [
            'id' => $userId,
            'username' => (string) $user['username'],
            'email' => (string) $user['email'],
            'name' => $fullName !== '' ? $fullName : (string) ($user['name'] ?? $user['username']),
            'first_name' => (string) $user['first_name'],
            'last_name' => (string) $user['last_name'],
            'roles' => $roles,
            'permissions' => $permissions,
        ];

        $this->audit->log($userId, 'LOGIN_SUCCESS', 'auth', $userId);

        return $sessionUser;
    }

    /**
     * @param array<string, mixed> $data
     * @return array{ok:true,user:array}|array{ok:false,message:string,errors:array<string,string>}
     */
    public function register(array $data): array
    {
        $firstName = trim((string) ($data['first_name'] ?? ''));
        $lastName = trim((string) ($data['last_name'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $username = trim((string) ($data['username'] ?? ''));
        $password = (string) ($data['password'] ?? '');
        $passwordConfirmation = (string) ($data['password_confirmation'] ?? '');

        $errors = [];

        if ($firstName === '') {
            $errors['first_name'] = 'El nombre es obligatorio.';
        }
        if ($lastName === '') {
            $errors['last_name'] = 'El apellido es obligatorio.';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Ingrese un correo válido.';
        }
        if ($username === '' || !preg_match('/^[A-Za-z0-9._-]{3,50}$/', $username)) {
            $errors['username'] = 'Usuario inválido (3-50 caracteres: letras, números, . _ -).';
        }
        if ($password !== $passwordConfirmation) {
            $errors['password_confirmation'] = 'La confirmación de contraseña no coincide.';
        }
        if (!$this->isValidPassword($password)) {
            $errors['password'] = 'La contraseña debe tener mínimo 8 caracteres, una mayúscula y un número.';
        }

        if ($email !== '' && $this->users->findByEmail($email) !== null) {
            $errors['email'] = 'El correo ya está registrado.';
        }
        if ($username !== '' && $this->users->findByUsername($username) !== null) {
            $errors['username'] = 'El nombre de usuario ya está en uso.';
        }

        if ($errors !== []) {
            return [
                'ok' => false,
                'message' => 'Revise los datos del formulario.',
                'errors' => $errors,
            ];
        }

        $id = $this->users->create([
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'status' => 'pending',
        ]);

        $user = $this->users->findById($id) ?? [
            'id' => $id,
            'username' => $username,
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'status' => 'pending',
            'name' => trim($firstName . ' ' . $lastName),
        ];

        unset($user['password']);

        $this->audit->log($id, 'USER_REGISTERED', 'user', $id, null, [
            'username' => $username,
            'email' => $email,
            'status' => 'pending',
        ]);

        return [
            'ok' => true,
            'user' => $user,
        ];
    }

    public function logout(?int $userId): void
    {
        if ($userId !== null) {
            $this->audit->log($userId, 'LOGOUT', 'auth', $userId);
        }
    }

    private function isValidPassword(string $password): bool
    {
        if (strlen($password) < 8) {
            return false;
        }

        return (bool) preg_match('/[A-Z]/', $password)
            && (bool) preg_match('/\d/', $password);
    }
}
