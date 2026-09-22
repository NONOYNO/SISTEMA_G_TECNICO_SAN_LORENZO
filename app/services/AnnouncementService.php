<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Database;
use InvalidArgumentException;
use PDO;
use RuntimeException;

final class AnnouncementService
{
    private PDO $pdo;
    private NotificationService $notifications;
    private AuditService $audit;

    public function __construct(?PDO $pdo = null, ?NotificationService $notifications = null, ?AuditService $audit = null)
    {
        $this->pdo = $pdo ?? Database::connection();
        $this->notifications = $notifications ?? new NotificationService($this->pdo);
        $this->audit = $audit ?? new AuditService($this->pdo);
    }

    /**
     * @param array{roles?:list<string>, can_manage?:bool, user_id?:int} $viewer
     * @return list<array<string, mixed>>
     */
    public function listForViewer(array $viewer, ?string $statusFilter = null): array
    {
        $canManage = (bool) ($viewer['can_manage'] ?? false);
        $roles = array_values(array_filter(array_map(
            static fn ($r): string => strtoupper(trim((string) $r)),
            is_array($viewer['roles'] ?? null) ? $viewer['roles'] : []
        )));

        $statusFilter = $statusFilter !== null ? strtolower(trim($statusFilter)) : null;
        if ($statusFilter !== null && !in_array($statusFilter, ['draft', 'published', 'archived'], true)) {
            $statusFilter = null;
        }

        // Más reciente primero (último enviado / publicado arriba).
        $orderBy = 'ORDER BY COALESCE(a.publish_at, a.updated_at, a.created_at) DESC, a.id DESC';

        if ($canManage) {
            $sql = 'SELECT a.*,
                           CONCAT(u.first_name, \' \', u.last_name) AS author_name,
                           COALESCE(a.publish_at, a.updated_at, a.created_at) AS sort_at
                    FROM announcements a
                    INNER JOIN users u ON u.id = a.author_id';
            $params = [];

            if ($statusFilter !== null) {
                $sql .= ' WHERE a.status = :status';
                $params['status'] = $statusFilter;
            }

            $sql .= ' ' . $orderBy;

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            return array_values($rows);
        }

        $stmt = $this->pdo->query(
            'SELECT a.*,
                    CONCAT(u.first_name, \' \', u.last_name) AS author_name,
                    COALESCE(a.publish_at, a.updated_at, a.created_at) AS sort_at
             FROM announcements a
             INNER JOIN users u ON u.id = a.author_id
             WHERE a.status = \'published\'
               AND (a.expire_at IS NULL OR a.expire_at >= NOW())
               AND (a.publish_at IS NULL OR a.publish_at <= NOW())
             ' . $orderBy
        );

        $rows = $stmt ? ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];

        return array_values(array_filter(
            $rows,
            fn (array $row): bool => $this->audienceMatches((string) ($row['audience'] ?? ''), $roles)
        ));
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT a.*,
                    CONCAT(u.first_name, \' \', u.last_name) AS author_name
             FROM announcements a
             INNER JOIN users u ON u.id = a.author_id
             WHERE a.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data, int $authorId): array
    {
        $payload = $this->validatePayload($data, false);
        $status = $payload['status'];

        if ($status === 'published' && !auth_can('announcements.publish')) {
            $status = 'draft';
            $payload['status'] = 'draft';
        }

        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO announcements
                    (author_id, title, description, content, category, priority, status,
                     publish_at, expire_at, audience, created_at, updated_at)
                 VALUES
                    (:author_id, :title, :description, :content, :category, :priority, :status,
                     :publish_at, :expire_at, :audience, NOW(), NOW())'
            );

            $stmt->execute([
                'author_id' => $authorId,
                'title' => $payload['title'],
                'description' => $payload['description'],
                'content' => $payload['content'],
                'category' => $payload['category'],
                'priority' => $payload['priority'],
                'status' => $status,
                'publish_at' => $status === 'published' ? date('Y-m-d H:i:s') : $payload['publish_at'],
                'expire_at' => $payload['expire_at'],
                'audience' => $payload['audience'],
            ]);

            $id = (int) $this->pdo->lastInsertId();
            $announcement = $this->find($id);

            if ($announcement === null) {
                throw new RuntimeException('No se pudo crear el aviso.');
            }

            $notificationsCreated = 0;
            if ($status === 'published') {
                $notificationsCreated = $this->fanOut($announcement);
            }

            $this->pdo->commit();

            $this->audit->log($authorId, 'announcements.create', 'announcement', $id, null, [
                'title' => $payload['title'],
                'status' => $status,
                'notifications_created' => $notificationsCreated,
            ]);

            $announcement['notifications_created'] = $notificationsCreated;

            return $announcement;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): array
    {
        $existing = $this->find($id);
        if ($existing === null) {
            throw new RuntimeException('Aviso no encontrado.');
        }

        $payload = $this->validatePayload($data, true, $existing);

        // No permitir publicar solo vía mass-assignment de status.
        if (($payload['status'] ?? $existing['status']) === 'published'
            && $existing['status'] !== 'published'
            && !auth_can('announcements.publish')
        ) {
            $payload['status'] = $existing['status'];
        }

        if (($payload['status'] ?? '') === 'archived' && !auth_can('announcements.publish')) {
            $payload['status'] = $existing['status'];
        }

        $stmt = $this->pdo->prepare(
            'UPDATE announcements SET
                title = :title,
                description = :description,
                content = :content,
                category = :category,
                priority = :priority,
                status = :status,
                publish_at = :publish_at,
                expire_at = :expire_at,
                audience = :audience,
                updated_at = NOW()
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
            'title' => $payload['title'],
            'description' => $payload['description'],
            'content' => $payload['content'],
            'category' => $payload['category'],
            'priority' => $payload['priority'],
            'status' => $payload['status'],
            'publish_at' => $payload['publish_at'],
            'expire_at' => $payload['expire_at'],
            'audience' => $payload['audience'],
        ]);

        $updated = $this->find($id);
        if ($updated === null) {
            throw new RuntimeException('Aviso no encontrado tras actualizar.');
        }

        $this->audit->log(auth_id(), 'announcements.update', 'announcement', $id, [
            'title' => $existing['title'],
            'status' => $existing['status'],
        ], [
            'title' => $updated['title'],
            'status' => $updated['status'],
        ]);

        return $updated;
    }

    public function delete(int $id): void
    {
        $existing = $this->find($id);
        if ($existing === null) {
            throw new RuntimeException('Aviso no encontrado.');
        }

        $diskPaths = [];

        $list = $this->pdo->prepare(
            'SELECT disk_path FROM attachments
             WHERE attachable_type = \'announcement\' AND attachable_id = :id'
        );
        $list->execute(['id' => $id]);
        foreach ($list->fetchAll(PDO::FETCH_COLUMN) ?: [] as $path) {
            $diskPaths[] = (string) $path;
        }

        $this->pdo->beginTransaction();

        try {
            $delAtt = $this->pdo->prepare(
                'DELETE FROM attachments
                 WHERE attachable_type = \'announcement\' AND attachable_id = :id'
            );
            $delAtt->execute(['id' => $id]);

            $delNotif = $this->pdo->prepare('DELETE FROM notifications WHERE announcement_id = :id');
            $delNotif->execute(['id' => $id]);

            $stmt = $this->pdo->prepare('DELETE FROM announcements WHERE id = :id');
            $stmt->execute(['id' => $id]);

            $this->pdo->commit();
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }

        $storageRoot = realpath(base_path('storage/uploads'));
        foreach ($diskPaths as $relative) {
            $relative = str_replace(['\\', '..'], ['/', ''], $relative);
            $absolute = base_path($relative);
            $realFile = realpath($absolute);
            if ($storageRoot !== false && $realFile !== false && str_starts_with($realFile, $storageRoot) && is_file($realFile)) {
                @unlink($realFile);
            }
        }

        $this->audit->log(auth_id(), 'announcements.delete', 'announcement', $id, [
            'title' => $existing['title'],
            'status' => $existing['status'],
            'attachments_removed' => count($diskPaths),
        ], null);
    }

    public function publish(int $id): array
    {
        $announcement = $this->find($id);
        if ($announcement === null) {
            throw new RuntimeException('Aviso no encontrado.');
        }

        if (trim((string) $announcement['title']) === '' || trim((string) $announcement['content']) === '') {
            throw new InvalidArgumentException('El aviso requiere título y contenido para publicarse.');
        }

        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare(
                'UPDATE announcements
                 SET status = \'published\',
                     publish_at = NOW(),
                     updated_at = NOW()
                 WHERE id = :id'
            );
            $stmt->execute(['id' => $id]);

            $published = $this->find($id);
            if ($published === null) {
                throw new RuntimeException('Aviso no encontrado tras publicar.');
            }

            $created = $this->fanOut($published);
            $this->pdo->commit();

            $this->audit->log(auth_id(), 'announcements.publish', 'announcement', $id, [
                'title' => $announcement['title'],
                'status' => $announcement['status'],
            ], [
                'title' => $announcement['title'],
                'status' => 'published',
                'notifications_created' => $created,
            ]);

            $published['notifications_created'] = $created;

            return $published;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function archive(int $id): array
    {
        $announcement = $this->find($id);
        if ($announcement === null) {
            throw new RuntimeException('Aviso no encontrado.');
        }

        $stmt = $this->pdo->prepare(
            'UPDATE announcements
             SET status = \'archived\', updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);

        $archived = $this->find($id);
        if ($archived === null) {
            throw new RuntimeException('Aviso no encontrado tras archivar.');
        }

        $this->audit->log(auth_id(), 'announcements.archive', 'announcement', $id, [
            'title' => $announcement['title'],
            'status' => $announcement['status'],
        ], [
            'title' => $announcement['title'],
            'status' => 'archived',
        ]);

        return $archived;
    }

    /**
     * Conteos para dashboard VICERRECTOR.
     *
     * @return array{mine:int,published:int,drafts:int,archived:int}
     */
    public function countsForAuthor(int $authorId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                COUNT(*) AS mine,
                SUM(CASE WHEN status = \'published\' THEN 1 ELSE 0 END) AS published,
                SUM(CASE WHEN status = \'draft\' THEN 1 ELSE 0 END) AS drafts,
                SUM(CASE WHEN status = \'archived\' THEN 1 ELSE 0 END) AS archived
             FROM announcements
             WHERE author_id = :author_id'
        );
        $stmt->execute(['author_id' => $authorId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'mine' => (int) ($row['mine'] ?? 0),
            'published' => (int) ($row['published'] ?? 0),
            'drafts' => (int) ($row['drafts'] ?? 0),
            'archived' => (int) ($row['archived'] ?? 0),
        ];
    }

    public function countAll(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM announcements')->fetchColumn();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function recentPublished(int $limit = 5): array
    {
        $limit = max(1, min(20, $limit));
        $stmt = $this->pdo->query(
            "SELECT id, title, description, priority, publish_at, category
             FROM announcements
             WHERE status = 'published'
               AND (expire_at IS NULL OR expire_at >= NOW())
               AND (publish_at IS NULL OR publish_at <= NOW())
             ORDER BY COALESCE(publish_at, updated_at, created_at) DESC, id DESC
             LIMIT {$limit}"
        );

        return $stmt ? ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
    }

    private function fanOut(array $announcement): int
    {
        $userIds = $this->resolveAudienceUserIds((string) ($announcement['audience'] ?? ''));

        return $this->notifications->fanOutFromAnnouncement($announcement, $userIds, $this->pdo);
    }

    /**
     * @return list<int>
     */
    public function resolveAudienceUserIds(string $audience): array
    {
        $audience = trim($audience);
        if ($audience === '' || strtoupper($audience) === 'ALL') {
            $stmt = $this->pdo->query(
                "SELECT id FROM users WHERE status = 'active'"
            );
            $ids = $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];

            return array_map('intval', $ids ?: []);
        }

        $roles = array_values(array_filter(array_map(
            static fn (string $r): string => strtoupper(trim($r)),
            explode(',', $audience)
        )));

        if ($roles === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($roles), '?'));
        $stmt = $this->pdo->prepare(
            "SELECT DISTINCT u.id
             FROM users u
             INNER JOIN user_roles ur ON ur.user_id = u.id
             INNER JOIN roles r ON r.id = ur.role_id
             WHERE u.status = 'active'
               AND UPPER(r.name) IN ({$placeholders})"
        );
        $stmt->execute($roles);
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

        return array_map('intval', $ids);
    }

    /**
     * @param list<string> $userRoles
     */
    public function audienceMatches(string $audience, array $userRoles): bool
    {
        $audience = trim($audience);
        if ($audience === '' || strtoupper($audience) === 'ALL') {
            return true;
        }

        $targets = array_map(
            static fn (string $r): string => strtoupper(trim($r)),
            explode(',', $audience)
        );
        $userRoles = array_map('strtoupper', $userRoles);

        foreach ($targets as $target) {
            if ($target !== '' && in_array($target, $userRoles, true)) {
                return true;
            }
        }

        return false;
    }

    public function canView(array $announcement, array $viewer): bool
    {
        if (!empty($viewer['can_manage'])) {
            return true;
        }

        if (($announcement['status'] ?? '') !== 'published') {
            return false;
        }

        $expireAt = $announcement['expire_at'] ?? null;
        if ($expireAt !== null && strtotime((string) $expireAt) < time()) {
            return false;
        }

        $roles = array_map('strtoupper', $viewer['roles'] ?? []);

        return $this->audienceMatches((string) ($announcement['audience'] ?? ''), $roles);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed>|null $existing
     * @return array<string, mixed>
     */
    private function validatePayload(array $data, bool $isUpdate, ?array $existing = null): array
    {
        $title = trim((string) ($data['title'] ?? ($existing['title'] ?? '')));
        $description = trim((string) ($data['description'] ?? ($existing['description'] ?? '')));
        $content = trim((string) ($data['content'] ?? ($existing['content'] ?? '')));
        $category = trim((string) ($data['category'] ?? ($existing['category'] ?? '')));
        $priority = strtolower(trim((string) ($data['priority'] ?? ($existing['priority'] ?? 'medium'))));
        $status = strtolower(trim((string) ($data['status'] ?? ($existing['status'] ?? 'draft'))));
        $audience = $this->normalizeAudience($data['audience'] ?? ($existing['audience'] ?? ''));
        $publishAt = $this->nullableDatetime($data['publish_at'] ?? ($existing['publish_at'] ?? null));
        $expireAt = $this->nullableDatetime($data['expire_at'] ?? ($existing['expire_at'] ?? null));

        if ($title === '' || mb_strlen($title) > 200) {
            throw new InvalidArgumentException('El título es obligatorio (máx. 200 caracteres).');
        }

        if ($description !== '' && mb_strlen($description) > 500) {
            throw new InvalidArgumentException('La descripción no puede superar 500 caracteres.');
        }

        if ($content === '' || mb_strlen($content) > 20000) {
            throw new InvalidArgumentException('El contenido es obligatorio (máx. 20000 caracteres).');
        }

        if ($category !== '' && mb_strlen($category) > 100) {
            throw new InvalidArgumentException('La categoría no puede superar 100 caracteres.');
        }

        if (!in_array($priority, ['low', 'medium', 'high'], true)) {
            throw new InvalidArgumentException('Prioridad inválida.');
        }

        if (!in_array($status, ['draft', 'published', 'archived'], true)) {
            throw new InvalidArgumentException('Estado inválido.');
        }

        if ($publishAt !== null && $expireAt !== null && strtotime($expireAt) < strtotime($publishAt)) {
            throw new InvalidArgumentException('La fecha de expiración debe ser posterior a la de publicación.');
        }

        return [
            'title' => $title,
            'description' => $description !== '' ? $description : null,
            'content' => $content,
            'category' => $category !== '' ? $category : null,
            'priority' => $priority,
            'status' => $status,
            'audience' => $audience !== '' ? $audience : null,
            'publish_at' => $publishAt,
            'expire_at' => $expireAt,
        ];
    }

    private function normalizeAudience(mixed $audience): string
    {
        if (is_array($audience)) {
            if (isset($audience['roles']) && is_array($audience['roles'])) {
                $audience = $audience['roles'];
            }
            $parts = array_map(
                static fn ($r): string => strtoupper(trim((string) $r)),
                $audience
            );

            return implode(',', array_values(array_filter($parts)));
        }

        $raw = trim((string) $audience);
        if ($raw === '') {
            return '';
        }

        if (strtoupper($raw) === 'ALL') {
            return 'ALL';
        }

        $parts = array_map(
            static fn (string $r): string => strtoupper(trim($r)),
            explode(',', $raw)
        );

        return implode(',', array_values(array_filter($parts)));
    }

    private function nullableDatetime(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = trim((string) $value);
        $ts = strtotime($value);

        if ($ts === false) {
            throw new InvalidArgumentException('Fecha inválida.');
        }

        return date('Y-m-d H:i:s', $ts);
    }
}
