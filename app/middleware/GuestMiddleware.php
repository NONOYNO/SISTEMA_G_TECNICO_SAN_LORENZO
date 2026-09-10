<?php

declare(strict_types=1);

namespace App\Middleware;

use Closure;

final class GuestMiddleware
{
    public function handle(array $params, Closure $next): void
    {
        if (auth_check()) {
            redirect('/dashboard');
        }

        $next($params);
    }
}
