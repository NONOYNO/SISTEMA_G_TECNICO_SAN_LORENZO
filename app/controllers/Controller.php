<?php

declare(strict_types=1);

namespace App\Controllers;

abstract class Controller
{
    protected function view(string $name, array $data = [], ?string $layout = 'main'): void
    {
        view($name, $data, $layout);
    }

    protected function redirect(string $path, int $status = 302): void
    {
        redirect($path, $status);
    }

    protected function json(array $payload, int $status = 200): void
    {
        json_response($payload, $status);
    }
}
