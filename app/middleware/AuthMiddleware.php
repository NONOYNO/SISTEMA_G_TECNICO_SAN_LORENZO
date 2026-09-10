<?php

declare(strict_types=1);

namespace App\Middleware;

use Closure;

final class AuthMiddleware
{
    public function handle(array $params, Closure $next): void
    {
        if (!auth_check()) {
            flash('error', 'Debe iniciar sesión para continuar.');
            redirect('/login');
        }

        $next($params);
    }
}
