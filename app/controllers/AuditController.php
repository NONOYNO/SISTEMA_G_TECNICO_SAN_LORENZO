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
        $q = trim((string) ($_GET['q'] ?? ''));
        $userId = trim((string) ($_GET['user_id'] ?? ''));
        $action = trim((string) ($_GET['action'] ?? ''));
        $entity = trim((string) ($_GET['entity'] ?? ''));
        $dateFrom = trim((string) ($_GET['date_from'] ?? ''));
        $dateTo = trim((string) ($_GET['date_to'] ?? ''));
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $filters = [
            'q' => $q,
            'user_id' => $userId,
            'action' => $action,
            'entity' => $entity,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];

        try {
            $pagination = $this->audit->paginate($filters, $page, 20);
            $statistics = $this->audit->getStatistics();
            $filterUsers = $this->audit->getFilterUsers();
            $filterActions = $this->audit->getFilterActions();
            $filterEntities = $this->audit->getFilterEntities();
        } catch (Throwable $e) {
            if ($e instanceof RuntimeException || str_contains($e->getMessage(), 'base de datos')) {
                flash('error', 'No se pudo cargar el registro de auditoría.');
                $pagination = [
                    'items' => [],
                    'total' => 0,
                    'page' => 1,
                    'per_page' => 20,
                    'pages' => 1,
                ];
                $statistics = [
                    'total_events' => 0,
                    'total_logins' => 0,
                    'total_announcements' => 0,
                    'total_modifications' => 0,
                ];
                $filterUsers = [];
                $filterActions = [];
                $filterEntities = [];
            } else {
                throw $e;
            }
        }

        $this->view('audit.index', [
            'title' => 'Auditoría',
            'heading' => 'Registro de auditoría',
            'description' => 'Trazabilidad de seguridad, autenticación y operaciones sensibles del sistema.',
            'logs' => $pagination['items'],
            'pagination' => $pagination,
            'statistics' => $statistics,
            'filterUsers' => $filterUsers,
            'filterActions' => $filterActions,
            'filterEntities' => $filterEntities,
            'filters' => $filters,
        ]);
    }
}

