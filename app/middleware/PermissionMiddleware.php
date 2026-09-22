<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Services\AuditService;
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
            try {
                $audit = new AuditService();
                $audit->log(
                    auth_id(),
                    'ACCESS_DENIED',
                    'permission',
                    null,
                    null,
                    [
                        'required_permission' => $this->permission,
                        'path' => $_SERVER['REQUEST_URI'] ?? '',
                        'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
                    ]
                );
            } catch (\Throwable $e) {
                error_log('PermissionMiddleware audit error: ' . $e->getMessage());
            }

            abort(403, 'No tiene permiso para realizar esta acción.');
        }

        $next($params);
    }
}

