<?php

declare(strict_types=1);

namespace App\Middleware;

use Closure;

final class CsrfMiddleware
{
    public function handle(array $params, Closure $next): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true) && !csrf_verify()) {
            if ($this->wantsJson()) {
                json_response([
                    'success' => false,
                    'message' => 'Token CSRF inválido o ausente.',
                    'data' => null,
                    'errors' => ['csrf' => ['Token CSRF inválido.']],
                ], 419);
            }

            flash('error', 'La sesión de seguridad expiró. Intente nuevamente.');
            $referer = $_SERVER['HTTP_REFERER'] ?? url('/login');
            redirect($referer);
        }

        $next($params);
    }

    private function wantsJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';

        return str_contains($accept, 'application/json')
            || strcasecmp($requestedWith, 'XMLHttpRequest') === 0
            || isset($_SERVER['HTTP_X_CSRF_TOKEN']);
    }
}
