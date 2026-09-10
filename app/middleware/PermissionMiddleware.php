<?php

declare(strict_types=1);

namespace App\Middleware;

use Closure;

final class PermissionMiddleware
{
    private string $permission;

    public function __construct(string $permission)
    {
        $this->permission = $permission;
    }

    public function handle(array $params, Closure $next): void
    {
        if (!auth_check()) {
            flash('error', 'Debe iniciar sesión para continuar.');
            redirect('/login');
        }

        if (!auth_can($this->permission)) {
            abort(403, 'No tiene permiso para realizar esta acción.');
        }

        $next($params);
    }
}
