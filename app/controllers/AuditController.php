<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuditService;
use RuntimeException;
use Throwable;

final class AuditController extends Controller
{
    private AuditService $audit;

    public function __construct(?AuditService $audit = null)
    {
        $this->audit = $audit ?? new AuditService();
    }

    public function index(): void
    {
        try {
            $logs = $this->audit->recent(50);
        } catch (Throwable $e) {
            if ($e instanceof RuntimeException || str_contains($e->getMessage(), 'base de datos')) {
                flash('error', 'No se pudo cargar el registro de auditoría.');
                $logs = [];
            } else {
                throw $e;
            }
        }

        $this->view('audit.index', [
            'title' => 'Auditoría',
            'heading' => 'Registro de auditoría',
            'description' => 'Últimos 50 eventos de seguridad y operaciones sensibles.',
            'logs' => $logs,
        ]);
    }
}
