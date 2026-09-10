<?php

declare(strict_types=1);

function csrf_token(): string
{
    if (empty($_SESSION['_csrf_token']) || !is_string($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf_token'];
}

function csrf_field(): string
{
    $token = e(csrf_token());

    return '<input type="hidden" name="_token" value="' . $token . '">';
}

function csrf_verify(?string $token = null): bool
{
    $sessionToken = $_SESSION['_csrf_token'] ?? '';

    if (!is_string($sessionToken) || $sessionToken === '') {
        return false;
    }

    if ($token === null) {
        $token = $_POST['_token']
            ?? $_SERVER['HTTP_X_CSRF_TOKEN']
            ?? '';
    }

    if (!is_string($token) || $token === '') {
        return false;
    }

    return hash_equals($sessionToken, $token);
}
