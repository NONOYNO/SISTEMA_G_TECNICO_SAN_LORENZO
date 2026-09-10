<?php

declare(strict_types=1);

namespace App\Controllers;

final class HomeController extends Controller
{
    public function index(): void
    {
        if (auth_check()) {
            $this->redirect('/dashboard');
        }

        $this->redirect('/login');
    }
}
