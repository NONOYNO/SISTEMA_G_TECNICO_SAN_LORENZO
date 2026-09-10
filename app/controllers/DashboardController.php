<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Database;
use App\Services\AnnouncementService;
use App\Services\NotificationService;
use PDO;
use Throwable;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $user = auth_user() ?? [];
        $userId = (int) ($user['id'] ?? 0);
        $metrics = [
            'role_view' => $this->resolveRoleView($user),
            'cards' => [],
            'recent_announcements' => [],
        ];

        try {
            $pdo = Database::connection();
            $announcements = new AnnouncementService($pdo);
            $notifications = new NotificationService($pdo);
            $roleView = $metrics['role_view'];

            if ($roleView === 'ADMIN') {
                $usersCount = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
                $annCount = $announcements->countAll();
                $unread = $userId > 0 ? $notifications->countUnread($userId) : 0;
                $auditRecent = 0;
                try {
                    $auditRecent = (int) $pdo->query(
                        'SELECT COUNT(*) FROM audit_logs WHERE created_at >= (NOW() - INTERVAL 7 DAY)'
                    )->fetchColumn();
                } catch (Throwable) {
                    $auditRecent = 0;
                }

                $metrics['cards'] = [
                    ['label' => 'Usuarios', 'value' => $usersCount, 'icon' => 'bi-people', 'hint' => 'Total registrados'],
                    ['label' => 'Avisos', 'value' => $annCount, 'icon' => 'bi-megaphone', 'hint' => 'Todos los estados'],
                    ['label' => 'No leídas', 'value' => $unread, 'icon' => 'bi-bell', 'hint' => 'Sus notificaciones'],
                    ['label' => 'Auditoría (7d)', 'value' => $auditRecent, 'icon' => 'bi-journal-text', 'hint' => 'Eventos recientes'],
                ];
            } elseif ($roleView === 'VICERRECTOR') {
                $counts = $announcements->countsForAuthor($userId);
                $metrics['cards'] = [
                    ['label' => 'Mis avisos', 'value' => $counts['mine'], 'icon' => 'bi-collection', 'hint' => 'Creados por usted'],
                    ['label' => 'Publicados', 'value' => $counts['published'], 'icon' => 'bi-check2-circle', 'hint' => 'Visibles a destinatarios'],
                    ['label' => 'Borradores', 'value' => $counts['drafts'], 'icon' => 'bi-pencil-square', 'hint' => 'Pendientes de publicar'],
                    ['label' => 'Archivados', 'value' => $counts['archived'], 'icon' => 'bi-archive', 'hint' => 'Histórico'],
                ];
            } else {
                $unread = $userId > 0 ? $notifications->countUnread($userId) : 0;
                $roles = array_map('strtoupper', is_array($user['roles'] ?? null) ? $user['roles'] : []);

                $stmt = $pdo->query(
                    "SELECT id, title, description, priority, publish_at, category, audience
                     FROM announcements
                     WHERE status = 'published'
                       AND (expire_at IS NULL OR expire_at >= NOW())
                       AND (publish_at IS NULL OR publish_at <= NOW())
                     ORDER BY COALESCE(publish_at, updated_at, created_at) DESC, id DESC
                     LIMIT 20"
                );
                $allRecent = $stmt ? ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
                $metrics['recent_announcements'] = array_slice(array_values(array_filter(
                    $allRecent,
                    static fn (array $row): bool => $announcements->audienceMatches(
                        (string) ($row['audience'] ?? ''),
                        $roles
                    )
                )), 0, 5);

                $metrics['cards'] = [
                    [
                        'label' => 'Avisos vigentes',
                        'value' => count($metrics['recent_announcements']),
                        'icon' => 'bi-megaphone',
                        'hint' => 'Publicados recientes',
                    ],
                    [
                        'label' => 'No leídas',
                        'value' => $unread,
                        'icon' => 'bi-bell',
                        'hint' => 'Notificaciones pendientes',
                    ],
                ];
            }
        } catch (Throwable) {
            $metrics['cards'] = [
                ['label' => 'Estado', 'value' => '—', 'icon' => 'bi-exclamation-triangle', 'hint' => 'No se pudieron cargar métricas'],
            ];
        }

        $this->view('dashboard.index', [
            'title' => 'Dashboard',
            'user' => $user,
            'metrics' => $metrics,
        ]);
    }

    /**
     * @param array<string, mixed> $user
     */
    private function resolveRoleView(array $user): string
    {
        $roles = array_map(
            static fn ($r): string => strtoupper((string) $r),
            is_array($user['roles'] ?? null) ? $user['roles'] : []
        );

        foreach (['ADMIN', 'VICERRECTOR', 'RECTOR', 'DOCENTE'] as $role) {
            if (in_array($role, $roles, true)) {
                return $role;
            }
        }

        return 'DOCENTE';
    }
}
