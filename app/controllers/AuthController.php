<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;

final class AuthController extends Controller
{
    private AuthService $auth;

    public function __construct(?AuthService $auth = null)
    {
        $this->auth = $auth ?? new AuthService();
    }

    public function showLogin(): void
    {
        $this->view('auth.login', [
            'title' => 'Iniciar sesión',
        ], 'auth');
    }

    public function login(): void
    {
        $login = trim((string) ($_POST['email'] ?? $_POST['login'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        flash_input(['login' => $login]);

        if ($login === '' || $password === '') {
            flash('error', 'Correo/usuario y contraseña son obligatorios.');
            $this->redirect('/login');
        }

        $user = $this->auth->attempt($login, $password);

        if ($user === false) {
            flash('error', 'Credenciales incorrectas o cuenta no disponible.');
            $this->redirect('/login');
        }

        auth_login($user);
        clear_old_input();
        flash('success', 'Bienvenido(a) al portal institucional.');
        $this->redirect('/dashboard');
    }

    public function showRegister(): void
    {
        $this->view('auth.register', [
            'title' => 'Registro',
        ], 'auth');
    }

    public function register(): void
    {
        $input = [
            'first_name' => trim((string) ($_POST['first_name'] ?? '')),
            'last_name' => trim((string) ($_POST['last_name'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'username' => trim((string) ($_POST['username'] ?? '')),
            'password' => (string) ($_POST['password'] ?? ''),
            'password_confirmation' => (string) ($_POST['password_confirmation'] ?? ''),
        ];

        flash_input([
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'email' => $input['email'],
            'username' => $input['username'],
        ]);

        $result = $this->auth->register($input);

        if (!$result['ok']) {
            flash('error', $result['message']);
            $_SESSION['_flash']['errors'] = $result['errors'] ?? [];
            // Si la cuenta se creó pero falló el auto-login, ir al login.
            if (($result['errors'] ?? []) === [] && str_contains((string) $result['message'], 'iniciar sesión')) {
                $this->redirect('/login');
            }
            $this->redirect('/register');
        }

        clear_old_input();
        auth_login($result['user']);
        flash('success', 'Registro exitoso. Bienvenido(a) al portal institucional.');
        $this->redirect('/dashboard');
    }

    public function logout(): void
    {
        $this->auth->logout(auth_id());
        auth_logout();
        flash('success', 'Sesión cerrada correctamente.');
        $this->redirect('/login');
    }
}
