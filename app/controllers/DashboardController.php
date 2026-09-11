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

            $metrics['vicerrector_activities'] = $this->getVicerrectorActivities($pdo);
        } catch (Throwable) {
            $metrics['cards'] = [
                ['label' => 'Estado', 'value' => '—', 'icon' => 'bi-exclamation-triangle', 'hint' => 'No se pudieron cargar métricas'],
            ];
            $metrics['vicerrector_activities'] = [
                'items' => [],
                'total_files' => 0,
                'total_announcements' => 0,
            ];
        }

        $this->view('dashboard.index', [
            'title' => 'Dashboard',
            'user' => $user,
            'metrics' => $metrics,
        ]);
    }

    /**
     * Obtiene las actividades recientes del Vicerrectorado (archivos adjuntos y comunicados emitidos).
     *
     * @return array{
     *   items: list<array<string, mixed>>,
     *   total_files: int,
     *   total_announcements: int
     * }
     */
    private function getVicerrectorActivities(PDO $pdo): array
    {
        $activities = [];
        $canManage = auth_can('announcements.create') || auth_can('announcements.edit');

        // 1. Archivos adjuntos y documentos institucionales
        try {
            $sqlFiles = "
                SELECT a.id, a.original_name, a.stored_name, a.mime_type, a.extension, a.size_bytes, a.created_at,
                       a.attachable_type, a.attachable_id,
                       COALESCE(NULLIF(TRIM(CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, ''))), ''), u.username, 'Vicerrectorado') AS author_name,
                       ann.title AS announcement_title,
                       ann.status AS announcement_status
                FROM attachments a
                LEFT JOIN users u ON u.id = a.uploaded_by
                LEFT JOIN announcements ann ON (a.attachable_type = 'announcement' AND ann.id = a.attachable_id)
                " . ($canManage ? "" : "WHERE ann.id IS NULL OR ann.status = 'published'") . "
                ORDER BY a.created_at DESC
                LIMIT 15
            ";
            $stmt = $pdo->query($sqlFiles);
            $files = $stmt ? ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
            foreach ($files as $f) {
                $activities[] = [
                    'id' => (int) $f['id'],
                    'type' => 'file',
                    'title' => (string) $f['original_name'],
                    'detail' => !empty($f['announcement_title']) ? (string) $f['announcement_title'] : 'Documento institucional',
                    'extension' => strtolower((string) ($f['extension'] ?? '')),
                    'size_bytes' => (int) ($f['size_bytes'] ?? 0),
                    'date' => (string) ($f['created_at'] ?? ''),
                    'author' => (string) ($f['author_name'] ?? 'Vicerrectorado'),
                    'attachable_id' => (int) ($f['attachable_id'] ?? 0),
                    'download_url' => url('/files/' . $f['id'] . '/download'),
                    'view_url' => !empty($f['attachable_id']) ? url('/announcements/' . $f['attachable_id']) : null,
                ];
            }
        } catch (Throwable) {
        }

        // 2. Avisos y comunicados oficiales del Vicerrectorado
        try {
            $sqlAnn = "
                SELECT ann.id, ann.title, ann.description, ann.category, ann.priority, ann.status,
                       ann.publish_at, ann.created_at,
                       COALESCE(NULLIF(TRIM(CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, ''))), ''), u.username, 'Vicerrectorado') AS author_name,
                       (SELECT COUNT(*) FROM attachments at WHERE at.attachable_type = 'announcement' AND at.attachable_id = ann.id) AS files_count
                FROM announcements ann
                LEFT JOIN users u ON u.id = ann.author_id
                " . ($canManage ? "" : "WHERE ann.status = 'published'") . "
                ORDER BY COALESCE(ann.publish_at, ann.created_at) DESC
                LIMIT 10
            ";
            $stmt = $pdo->query($sqlAnn);
            $announcements = $stmt ? ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
            foreach ($announcements as $a) {
                $activities[] = [
                    'id' => (int) $a['id'],
                    'type' => 'announcement',
                    'title' => (string) $a['title'],
                    'detail' => !empty($a['description']) ? (string) $a['description'] : ((string) ($a['category'] ?? 'Comunicado institucional')),
                    'extension' => '',
                    'size_bytes' => 0,
                    'date' => (string) ($a['publish_at'] ?? $a['created_at'] ?? ''),
                    'author' => (string) ($a['author_name'] ?? 'Vicerrectorado'),
                    'attachable_id' => (int) $a['id'],
                    'download_url' => null,
                    'view_url' => url('/announcements/' . $a['id']),
                    'files_count' => (int) ($a['files_count'] ?? 0),
                    'priority' => (string) ($a['priority'] ?? 'medium'),
                    'category' => (string) ($a['category'] ?? 'Académico'),
                ];
            }
        } catch (Throwable) {
        }

        // Ordenar unificado por fecha DESC
        usort($activities, static function (array $x, array $y): int {
            return strcmp((string) ($y['date'] ?? ''), (string) ($x['date'] ?? ''));
        });

        $totalFiles = 0;
        $totalAnnouncements = 0;
        try {
            $totalFiles = (int) $pdo->query("SELECT COUNT(*) FROM attachments")->fetchColumn();
            $totalAnnouncements = (int) $pdo->query("SELECT COUNT(*) FROM announcements WHERE status = 'published'")->fetchColumn();
        } catch (Throwable) {
        }

        return [
            'items' => array_slice($activities, 0, 15),
            'total_files' => $totalFiles,
            'total_announcements' => $totalAnnouncements,
        ];
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
