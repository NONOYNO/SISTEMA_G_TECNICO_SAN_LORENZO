<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Database;
use PDO;
use Throwable;

final class AuditService
{
    private PDO $pdo;
    private array $announcementCache = [];
    private array $userCache = [];
    private array $attachmentCache = [];
    private array $roleCache = [];

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    public function log(
        ?int $userId,
        string $action,
        ?string $entity = null,
        ?int $entityId = null,
        ?array $old = null,
        ?array $new = null
    ): void {
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            if (is_string($ip) && strlen($ip) > 45) {
                $ip = substr($ip, 0, 45);
            }

            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            if (is_string($userAgent) && strlen($userAgent) > 255) {
                $userAgent = substr($userAgent, 0, 255);
            }

            $stmt = $this->pdo->prepare(
                'INSERT INTO audit_logs
                    (user_id, action, entity, entity_id, old_values, new_values, ip, user_agent, created_at)
                 VALUES
                    (:user_id, :action, :entity, :entity_id, :old_values, :new_values, :ip, :user_agent, NOW())'
            );

            $stmt->execute([
                'user_id' => $userId,
                'action' => $action,
                'entity' => $entity,
                'entity_id' => $entityId,
                'old_values' => $old === null ? null : json_encode($old, JSON_UNESCAPED_UNICODE),
                'new_values' => $new === null ? null : json_encode($new, JSON_UNESCAPED_UNICODE),
                'ip' => is_string($ip) ? $ip : null,
                'user_agent' => is_string($userAgent) ? $userAgent : null,
            ]);
        } catch (Throwable $e) {
            error_log('AuditService::log failed: ' . $e->getMessage());
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function recent(int $limit = 50): array
    {
        $limit = max(1, min(200, $limit));

        $stmt = $this->pdo->prepare(
            'SELECT a.id, a.user_id, a.action, a.entity, a.entity_id,
                    a.old_values, a.new_values, a.ip, a.user_agent, a.created_at,
                    u.username,
                    TRIM(CONCAT(COALESCE(u.first_name, \'\'), \' \', COALESCE(u.last_name, \'\'))) AS user_name,
                    (
                        SELECT GROUP_CONCAT(r.display_name SEPARATOR \', \')
                        FROM user_roles ur
                        JOIN roles r ON r.id = ur.role_id
                        WHERE ur.user_id = a.user_id
                    ) AS user_roles
             FROM audit_logs a
             LEFT JOIN users u ON u.id = a.user_id
             ORDER BY a.created_at DESC, a.id DESC
             LIMIT ' . $limit
        );
        $stmt->execute();

        /** @var list<array<string, mixed>> $rows */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([$this, 'enrichLogEntry'], $rows);
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{items: list<array<string, mixed>>, total: int, page: int, per_page: int, pages: int}
     */
    public function paginate(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $perPage = max(1, min(100, $perPage));
        $conditions = [];
        $params = [];

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $conditions[] = '(
                u.username LIKE :q1 OR
                u.first_name LIKE :q2 OR
                u.last_name LIKE :q3 OR
                a.action LIKE :q4 OR
                a.entity LIKE :q5 OR
                a.ip LIKE :q6 OR
                a.old_values LIKE :q7 OR
                a.new_values LIKE :q8
            )';
            $searchTerm = '%' . $q . '%';
            for ($i = 1; $i <= 8; $i++) {
                $params['q' . $i] = $searchTerm;
            }
        }

        $userId = $filters['user_id'] ?? null;
        if ($userId !== null && $userId !== '' && is_numeric($userId)) {
            $conditions[] = 'a.user_id = :user_id';
            $params['user_id'] = (int) $userId;
        }

        $action = trim((string) ($filters['action'] ?? ''));
        if ($action !== '') {
            $conditions[] = 'a.action = :action';
            $params['action'] = $action;
        }

        $entity = trim((string) ($filters['entity'] ?? ''));
        if ($entity !== '') {
            $conditions[] = 'a.entity = :entity';
            $params['entity'] = $entity;
        }

        $dateFrom = trim((string) ($filters['date_from'] ?? ''));
        if ($dateFrom !== '') {
            $conditions[] = 'a.created_at >= :date_from';
            $params['date_from'] = $dateFrom . ' 00:00:00';
        }

        $dateTo = trim((string) ($filters['date_to'] ?? ''));
        if ($dateTo !== '') {
            $conditions[] = 'a.created_at <= :date_to';
            $params['date_to'] = $dateTo . ' 23:59:59';
        }

        $where = $conditions !== [] ? 'WHERE ' . implode(' AND ', $conditions) : '';

        // Contar registros totales
        $countSql = "SELECT COUNT(*) FROM audit_logs a LEFT JOIN users u ON u.id = a.user_id {$where}";
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($pages, $page));
        $offset = ($page - 1) * $perPage;

        // Obtener registros paginados
        $dataSql = "SELECT a.id, a.user_id, a.action, a.entity, a.entity_id,
                           a.old_values, a.new_values, a.ip, a.user_agent, a.created_at,
                           u.username,
                           TRIM(CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, ''))) AS user_name,
                           (
                               SELECT GROUP_CONCAT(r.display_name SEPARATOR ', ')
                               FROM user_roles ur
                               JOIN roles r ON r.id = ur.role_id
                               WHERE ur.user_id = a.user_id
                           ) AS user_roles
                    FROM audit_logs a
                    LEFT JOIN users u ON u.id = a.user_id
                    {$where}
                    ORDER BY a.created_at DESC, a.id DESC
                    LIMIT :limit OFFSET :offset";

        $dataStmt = $this->pdo->prepare($dataSql);
        foreach ($params as $key => $val) {
            $dataStmt->bindValue(':' . $key, $val);
        }
        $dataStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $dataStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $dataStmt->execute();

        /** @var list<array<string, mixed>> $rows */
        $rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

        $items = array_map([$this, 'enrichLogEntry'], $rows);

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'pages' => $pages,
        ];
    }

    /**
     * Estadísticas dinámicas de auditoría basadas en los datos reales.
     *
     * @return array{total_events: int, total_logins: int, total_announcements: int, total_modifications: int}
     */
    public function getStatistics(): array
    {
        $stmt = $this->pdo->query(
            'SELECT
                COUNT(*) AS total_events,
                SUM(CASE WHEN action = "LOGIN_SUCCESS" THEN 1 ELSE 0 END) AS total_logins,
                SUM(CASE WHEN action IN ("announcements.create", "ANNOUNCEMENT_CREATED") THEN 1 ELSE 0 END) AS total_announcements,
                SUM(CASE WHEN action LIKE "%update%" OR action LIKE "%UPDATED%" OR action = "USER_STATUS_CHANGED" OR action = "ROLE_PERMISSIONS_UPDATED" THEN 1 ELSE 0 END) AS total_modifications
             FROM audit_logs'
        );

        $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : [];

        return [
            'total_events' => (int) ($row['total_events'] ?? 0),
            'total_logins' => (int) ($row['total_logins'] ?? 0),
            'total_announcements' => (int) ($row['total_announcements'] ?? 0),
            'total_modifications' => (int) ($row['total_modifications'] ?? 0),
        ];
    }

    /**
     * Lista de usuarios para el filtro de auditoría.
     *
     * @return list<array{id: int, username: string, name: string}>
     */
    public function getFilterUsers(): array
    {
        $stmt = $this->pdo->query(
            'SELECT u.id, u.username,
                    TRIM(CONCAT(COALESCE(u.first_name, \'\'), \' \', COALESCE(u.last_name, \'\'))) AS name
             FROM users u
             ORDER BY u.first_name ASC, u.last_name ASC, u.username ASC'
        );

        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * Lista de acciones registradas para el filtro con nombres legibles.
     *
     * @return list<array{action: string, label: string}>
     */
    public function getFilterActions(): array
    {
        $stmt = $this->pdo->query(
            'SELECT DISTINCT action FROM audit_logs ORDER BY action ASC'
        );

        $actions = $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
        $result = [];

        foreach ($actions as $act) {
            $result[] = [
                'action' => (string) $act,
                'label' => $this->getActionLabel((string) $act),
            ];
        }

        usort($result, static fn ($a, $b) => strcmp($a['label'], $b['label']));

        return $result;
    }

    /**
     * Lista de entidades registradas para el filtro con nombres legibles.
     *
     * @return list<array{entity: string, label: string}>
     */
    public function getFilterEntities(): array
    {
        $stmt = $this->pdo->query(
            'SELECT DISTINCT entity FROM audit_logs WHERE entity IS NOT NULL AND entity != \'\' ORDER BY entity ASC'
        );

        $entities = $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
        $result = [];

        foreach ($entities as $ent) {
            $result[] = [
                'entity' => (string) $ent,
                'label' => $this->getEntityLabel((string) $ent),
            ];
        }

        usort($result, static fn ($a, $b) => strcmp($a['label'], $b['label']));

        return $result;
    }

    /**
     * Enriquecer un registro de auditoría con representaciones amigables y seguras.
     *
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    public function enrichLogEntry(array $row): array
    {
        $rawAction = (string) ($row['action'] ?? '');
        $rawEntity = (string) ($row['entity'] ?? '');
        $entityId = isset($row['entity_id']) && $row['entity_id'] !== '' ? (int) $row['entity_id'] : null;

        $oldValues = null;
        if (!empty($row['old_values']) && is_string($row['old_values'])) {
            $decoded = json_decode($row['old_values'], true);
            if (is_array($decoded)) {
                $oldValues = $decoded;
            }
        }

        $newValues = null;
        if (!empty($row['new_values']) && is_string($row['new_values'])) {
            $decoded = json_decode($row['new_values'], true);
            if (is_array($decoded)) {
                $newValues = $decoded;
            }
        }

        $displayName = trim((string) ($row['user_name'] ?? ''));
        if ($displayName === '') {
            $displayName = (string) ($row['username'] ?? '');
        }
        if ($displayName === '' && empty($row['user_id'])) {
            $displayName = '—';
        }

        $userRoles = trim((string) ($row['user_roles'] ?? ''));

        $actionLabel = $this->getActionLabel($rawAction);
        $actionBadge = $this->getActionBadge($rawAction);
        $entityLabel = $this->getEntityLabel($rawEntity);
        $entityDisplay = $this->formatEntityDisplay($rawEntity, $entityId, $newValues, $oldValues);

        $isFailure = in_array($rawAction, ['LOGIN_FAILED', 'ACCESS_DENIED'], true)
            || (isset($newValues['reason']) && !empty($newValues['reason']));

        $resultStatus = $isFailure ? 'fail' : 'success';
        $resultLabel = $isFailure ? 'Fallido' : 'Exitoso';
        $resultBadge = $isFailure ? 'danger' : 'success';

        $summary = $this->generateOperationSummary($row, $oldValues, $newValues);

        return array_merge($row, [
            'display_name' => $displayName,
            'user_roles' => $userRoles !== '' ? $userRoles : null,
            'action_label' => $actionLabel,
            'action_badge' => $actionBadge,
            'entity_label' => $entityLabel,
            'entity_display' => $entityDisplay,
            'parsed_old' => $oldValues,
            'parsed_new' => $newValues,
            'result_status' => $resultStatus,
            'result_label' => $resultLabel,
            'result_badge' => $resultBadge,
            'summary' => $summary,
        ]);
    }

    public function getActionLabel(string $action): string
    {
        return match ($action) {
            'LOGIN_SUCCESS' => 'Inicio de sesión',
            'LOGOUT' => 'Cierre de sesión',
            'LOGIN_FAILED' => 'Inicio de sesión fallido',
            'ACCESS_DENIED' => 'Acceso denegado',
            'announcements.create', 'CREATE_ANNOUNCEMENT' => 'Aviso creado',
            'announcements.update', 'UPDATE_ANNOUNCEMENT' => 'Aviso modificado',
            'announcements.publish', 'ANNOUNCEMENT_PUBLISHED' => 'Aviso publicado',
            'announcements.archive', 'ANNOUNCEMENT_ARCHIVED' => 'Aviso archivado',
            'announcements.delete', 'DELETE_ANNOUNCEMENT' => 'Aviso eliminado',
            'files.upload', 'UPLOAD_FILE' => 'Archivo cargado',
            'files.download', 'DOWNLOAD_FILE' => 'Archivo descargado',
            'files.delete', 'DELETE_FILE' => 'Archivo eliminado',
            'USER_CREATED', 'CREATE_USER' => 'Usuario creado',
            'USER_UPDATED', 'UPDATE_USER' => 'Usuario modificado',
            'USER_STATUS_CHANGED' => 'Estado de usuario modificado',
            'USER_REGISTERED' => 'Usuario registrado',
            'ROLE_ASSIGNED' => 'Rol asignado',
            'ROLE_CREATED' => 'Rol creado',
            'ROLE_PERMISSIONS_UPDATED' => 'Permisos de rol actualizados',
            'ROLE_DELETED' => 'Rol eliminado',
            default => str_replace(['.', '_'], ' ', ucfirst(strtolower($action))),
        };
    }

    public function getActionBadge(string $action): string
    {
        return match ($action) {
            'LOGIN_SUCCESS', 'announcements.publish', 'ANNOUNCEMENT_PUBLISHED' => 'success',
            'announcements.create', 'CREATE_ANNOUNCEMENT', 'files.upload', 'UPLOAD_FILE',
            'USER_CREATED', 'CREATE_USER', 'ROLE_ASSIGNED', 'ROLE_CREATED' => 'primary',
            'announcements.update', 'UPDATE_ANNOUNCEMENT', 'files.download', 'DOWNLOAD_FILE',
            'USER_UPDATED', 'UPDATE_USER', 'USER_REGISTERED', 'ROLE_PERMISSIONS_UPDATED' => 'info text-dark',
            'USER_STATUS_CHANGED' => 'warning text-dark',
            'LOGOUT', 'announcements.archive', 'ANNOUNCEMENT_ARCHIVED', 'ROLE_DELETED' => 'secondary',
            'LOGIN_FAILED', 'ACCESS_DENIED', 'announcements.delete', 'DELETE_ANNOUNCEMENT',
            'files.delete', 'DELETE_FILE' => 'danger',
            default => 'secondary',
        };
    }

    public function getEntityLabel(string $entity): string
    {
        return match (strtolower($entity)) {
            'announcement' => 'Aviso',
            'attachment', 'file' => 'Archivo',
            'user' => 'Usuario',
            'auth' => 'Autenticación',
            'role' => 'Rol',
            'permission' => 'Permiso',
            default => ucfirst($entity),
        };
    }

    /**
     * Construye una representación clara de la entidad afectada sin inventar información.
     */
    private function formatEntityDisplay(
        string $entity,
        ?int $entityId,
        ?array $newValues,
        ?array $oldValues
    ): string {
        $entityType = $this->getEntityLabel($entity);

        if ($entity === 'auth') {
            return $entityId !== null ? "Autenticación #{$entityId}" : 'Autenticación';
        }

        if ($entity === 'announcement') {
            $title = $newValues['title'] ?? $oldValues['title'] ?? null;
            if ($title === null && $entityId !== null) {
                $title = $this->getAnnouncementTitle($entityId);
            }
            $base = $entityId !== null ? "Aviso #{$entityId}" : 'Aviso';
            return $title !== null && $title !== '' ? "{$base} · {$title}" : $base;
        }

        if ($entity === 'attachment' || $entity === 'file') {
            $filename = $newValues['original_name'] ?? $oldValues['original_name'] ?? null;
            if ($filename === null && $entityId !== null) {
                $filename = $this->getAttachmentName($entityId);
            }
            $base = $entityId !== null ? "Archivo #{$entityId}" : 'Archivo';
            return $filename !== null && $filename !== '' ? "{$base} · {$filename}" : $base;
        }

        if ($entity === 'user') {
            $name = null;
            if (!empty($newValues['first_name']) || !empty($newValues['last_name'])) {
                $name = trim(($newValues['first_name'] ?? '') . ' ' . ($newValues['last_name'] ?? ''));
            }
            if (($name === null || $name === '') && !empty($newValues['username'])) {
                $name = (string) $newValues['username'];
            }
            if (($name === null || $name === '') && !empty($oldValues['username'])) {
                $name = (string) $oldValues['username'];
            }
            if ($name === null && $entityId !== null) {
                $name = $this->getUserName($entityId);
            }
            $base = $entityId !== null ? "Usuario #{$entityId}" : 'Usuario';
            return $name !== null && $name !== '' ? "{$base} · {$name}" : $base;
        }

        if ($entity === 'role') {
            $roleName = $newValues['name'] ?? $oldValues['name'] ?? null;
            $base = $entityId !== null ? "Rol #{$entityId}" : 'Rol';
            return $roleName !== null && $roleName !== '' ? "{$base} · {$roleName}" : $base;
        }

        if ($entityId !== null) {
            return "{$entityType} #{$entityId}";
        }

        return $entityType !== '' ? $entityType : '—';
    }

    private function getAnnouncementTitle(int $id): ?string
    {
        if (array_key_exists($id, $this->announcementCache)) {
            return $this->announcementCache[$id];
        }

        try {
            $stmt = $this->pdo->prepare('SELECT title FROM announcements WHERE id = :id');
            $stmt->execute(['id' => $id]);
            $title = $stmt->fetchColumn();
            $this->announcementCache[$id] = is_string($title) ? $title : null;
        } catch (Throwable) {
            $this->announcementCache[$id] = null;
        }

        return $this->announcementCache[$id];
    }

    private function getAttachmentName(int $id): ?string
    {
        if (array_key_exists($id, $this->attachmentCache)) {
            return $this->attachmentCache[$id];
        }

        try {
            $stmt = $this->pdo->prepare('SELECT original_name FROM attachments WHERE id = :id');
            $stmt->execute(['id' => $id]);
            $name = $stmt->fetchColumn();
            $this->attachmentCache[$id] = is_string($name) ? $name : null;
        } catch (Throwable) {
            $this->attachmentCache[$id] = null;
        }

        return $this->attachmentCache[$id];
    }

    private function getUserName(int $id): ?string
    {
        if (array_key_exists($id, $this->userCache)) {
            return $this->userCache[$id];
        }

        try {
            $stmt = $this->pdo->prepare('SELECT username, first_name, last_name FROM users WHERE id = :id');
            $stmt->execute(['id' => $id]);
            $u = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($u) {
                $fn = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
                $this->userCache[$id] = $fn !== '' ? "{$fn} (@{$u['username']})" : (string) $u['username'];
            } else {
                $this->userCache[$id] = null;
            }
        } catch (Throwable) {
            $this->userCache[$id] = null;
        }

        return $this->userCache[$id];
    }

    /**
     * Genera un resumen legible de la operación.
     */
    private function generateOperationSummary(array $row, ?array $old, ?array $new): string
    {
        $action = (string) ($row['action'] ?? '');

        return match ($action) {
            'LOGIN_SUCCESS' => 'Inicio de sesión satisfactorio en la plataforma institucional.',
            'LOGOUT' => 'Cierre de sesión finalizado correctamente.',
            'LOGIN_FAILED' => isset($new['reason'])
                ? 'Intento fallido de autenticación. Causa: ' . match ($new['reason']) {
                    'bad_password' => 'Contraseña incorrecta (Intento ' . ($new['attempts'] ?? 1) . ').',
                    'inactive_or_pending' => 'La cuenta se encuentra inactiva o pendiente de aprobación.',
                    'locked' => 'La cuenta se encuentra temporalmente bloqueada por exceso de intentos.',
                    default => (string) $new['reason'],
                } . (isset($new['login']) ? ' [Usuario ingresado: ' . $new['login'] . ']' : '')
                : 'Intento de inicio de sesión no reconocido o fallido' . (isset($new['login']) ? ' [Usuario: ' . $new['login'] . ']' : '') . '.',
            'ACCESS_DENIED' => 'Acceso denegado a recurso protegido por falta de permisos requeridos' . (isset($new['required_permission']) ? ' (' . $new['required_permission'] . ')' : '') . '.',
            'announcements.create', 'ANNOUNCEMENT_CREATED' => 'Aviso institucional creado exitosamente' . (isset($new['title']) ? ': "' . $new['title'] . '"' : '') . (isset($new['status']) ? ' (Estado: ' . $new['status'] . ')' : '') . '.',
            'announcements.update', 'ANNOUNCEMENT_UPDATED' => 'Aviso institucional actualizado' . (isset($new['title']) ? ': "' . $new['title'] . '"' : '') . '.',
            'announcements.publish', 'ANNOUNCEMENT_PUBLISHED' => 'Aviso institucional publicado en el portal' . (isset($new['title']) ? ': "' . $new['title'] . '"' : '') . (isset($new['notifications_created']) ? ' con ' . $new['notifications_created'] . ' notificación(es) generada(s)' : '') . '.',
            'announcements.archive', 'ANNOUNCEMENT_ARCHIVED' => 'Aviso archivado del catálogo vigente' . (isset($old['title']) ? ': "' . $old['title'] . '"' : '') . '.',
            'announcements.delete', 'DELETE_ANNOUNCEMENT' => 'Aviso institucional eliminado' . (isset($old['title']) ? ': "' . $old['title'] . '"' : '') . '.',
            'files.upload', 'FILE_UPLOADED' => 'Archivo cargado en el servidor' . (isset($new['original_name']) ? ': "' . $new['original_name'] . '"' : '') . (isset($new['size_bytes']) ? ' (' . round($new['size_bytes'] / 1024, 1) . ' KB)' : '') . '.',
            'files.download', 'DOWNLOAD_FILE' => 'Descarga de archivo adjunto' . (isset($new['original_name']) ? ': "' . $new['original_name'] . '"' : '') . '.',
            'files.delete', 'FILE_DELETED' => 'Archivo adjunto eliminado del almacenamiento' . (isset($old['original_name']) ? ': "' . $old['original_name'] . '"' : '') . '.',
            'USER_CREATED', 'CREATE_USER' => 'Nuevo usuario creado en el sistema' . (isset($new['username']) ? ': @' . $new['username'] : '') . (isset($new['email']) ? ' (' . $new['email'] . ')' : '') . '.',
            'USER_UPDATED', 'UPDATE_USER' => 'Datos de usuario modificados' . (isset($old['username']) ? ' para @' . $old['username'] : '') . '.',
            'USER_STATUS_CHANGED' => 'Estado de cuenta modificado' . (isset($new['status']) ? ' a "' . $new['status'] . '"' : '') . (isset($old['status']) ? ' (Anterior: "' . $old['status'] . '")' : '') . '.',
            'USER_REGISTERED' => 'Registro público de nueva cuenta institucional' . (isset($new['username']) ? ': @' . $new['username'] : '') . '.',
            'ROLE_ASSIGNED' => 'Actualización de roles de acceso asignados al usuario.',
            'ROLE_CREATED' => 'Nuevo rol de seguridad creado en la plataforma.',
            'ROLE_PERMISSIONS_UPDATED' => 'Permisos asignados al rol modificados.',
            'ROLE_DELETED' => 'Rol de seguridad eliminado del sistema.',
            default => 'Operación registrada en auditoría.',
        };
    }
}
