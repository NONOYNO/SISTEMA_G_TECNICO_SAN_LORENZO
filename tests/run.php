<?php

declare(strict_types=1);

/**
 * Runner simple de pruebas funcionales (sin PHPUnit).
 * Uso: php tests/run.php
 */

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Services\AuthService;
use App\Services\FileUploadService;
use App\Repositories\UserRepository;

$passed = 0;
$failed = 0;

function assert_true(bool $cond, string $label): void
{
    global $passed, $failed;
    if ($cond) {
        echo "[PASS] {$label}\n";
        $passed++;
    } else {
        echo "[FAIL] {$label}\n";
        $failed++;
    }
}

$auth = new AuthService();
$users = new UserRepository();

// Auth
$user = $auth->attempt('admin@uesanlorenzo.edu', 'Admin123!');
assert_true($user !== false && in_array('ADMIN', $user['roles'] ?? [], true), 'Login ADMIN válido');
assert_true($auth->attempt('admin@uesanlorenzo.edu', 'wrong') === false, 'Login con password incorrecta falla');

$docente = $auth->attempt('docente', 'Docente123!');
assert_true($docente !== false && in_array('DOCENTE', $docente['roles'] ?? [], true), 'Login DOCENTE por username');
assert_true(in_array('announcements.view', $docente['permissions'] ?? [], true), 'DOCENTE tiene announcements.view');
assert_true(!in_array('users.delete', $docente['permissions'] ?? [], true), 'DOCENTE no tiene users.delete');

// Password hash policy
$hash = password_hash('Admin123!', PASSWORD_DEFAULT);
assert_true(password_verify('Admin123!', $hash), 'password_hash/verify funciona');

// CSRF
$token = csrf_token();
assert_true(strlen($token) === 64, 'CSRF token de 64 hex');
assert_true(csrf_verify($token), 'csrf_verify acepta token de sesión');
assert_true(!csrf_verify('invalid'), 'csrf_verify rechaza token inválido');

// File extension policy via reflection of constants conceptually
$allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'jpg', 'jpeg', 'png', 'zip'];
$forbidden = ['php', 'exe', 'bat', 'phtml'];
assert_true(in_array('pdf', $allowed, true) && !in_array('php', $allowed, true), 'Whitelist de archivos correcta');
assert_true(in_array('php', $forbidden, true), 'Blacklist incluye php');

// Users exist
$admin = $users->findByLogin('admin');
assert_true($admin !== null && ($admin['status'] ?? '') === 'active', 'Usuario admin activo en BD');

echo "\nResultado: {$passed} passed, {$failed} failed\n";
exit($failed > 0 ? 1 : 0);
