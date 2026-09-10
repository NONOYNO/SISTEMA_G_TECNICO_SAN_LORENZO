<?php

declare(strict_types=1);

function auth_user(): ?array
{
    $user = $_SESSION['user'] ?? null;

    return is_array($user) ? $user : null;
}

function auth_check(): bool
{
    return auth_user() !== null;
}

function auth_id(): ?int
{
    $user = auth_user();

    if ($user === null || !isset($user['id'])) {
        return null;
    }

    return (int) $user['id'];
}

function auth_can(string $permission): bool
{
    $user = auth_user();

    if ($user === null) {
        return false;
    }

    $permissions = $user['permissions'] ?? [];

    if (!is_array($permissions)) {
        return false;
    }

    if (in_array('*', $permissions, true) || in_array('*.*', $permissions, true)) {
        return true;
    }

    return in_array($permission, $permissions, true);
}

function auth_has_role(string $role): bool
{
    $user = auth_user();

    if ($user === null) {
        return false;
    }

    $roles = $user['roles'] ?? [];

    if (!is_array($roles)) {
        return false;
    }

    $role = strtoupper($role);

    foreach ($roles as $item) {
        if (is_string($item) && strtoupper($item) === $role) {
            return true;
        }
    }

    return false;
}

function auth_login(array $user): void
{
    regenerate_session();
    $_SESSION['user'] = $user;
}

function auth_logout(): void
{
    unset($_SESSION['user']);
    regenerate_session();
}
