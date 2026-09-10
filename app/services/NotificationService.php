<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Database;
use PDO;
use RuntimeException;

final class NotificationService
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listForUser(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT n.*,
                    CASE WHEN nr.read_at IS NULL THEN 0 ELSE 1 END AS is_read,
                    nr.read_at
             FROM notifications n
             LEFT JOIN notification_reads nr
                ON nr.notification_id = n.id AND nr.user_id = :uid_join
             WHERE n.user_id = :uid
             ORDER BY is_read ASC, n.created_at DESC, n.id DESC'
        );
        $stmt->execute([
            'uid_join' => $userId,
            'uid' => $userId,
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        foreach ($rows as &$row) {
            $row['is_read'] = (int) ($row['is_read'] ?? 0) === 1;
            $row['id'] = (int) $row['id'];
            $row['user_id'] = (int) $row['user_id'];
            $row['announcement_id'] = $row['announcement_id'] !== null
                ? (int) $row['announcement_id']
                : null;
        }
        unset($row);

        return $rows;
    }

    public function markRead(int $notificationId, int $userId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT id FROM notifications WHERE id = :id AND user_id = :user_id LIMIT 1'
        );
        $stmt->execute([
            'id' => $notificationId,
            'user_id' => $userId,
        ]);

        if ($stmt->fetchColumn() === false) {
            throw new RuntimeException('Notificación no encontrada.');
        }

        $insert = $this->pdo->prepare(
            'INSERT INTO notification_reads (notification_id, user_id, read_at)
             VALUES (:notification_id, :user_id, NOW())
             ON DUPLICATE KEY UPDATE read_at = VALUES(read_at)'
        );

        return $insert->execute([
            'notification_id' => $notificationId,
            'user_id' => $userId,
        ]);
    }

    public function countUnread(int $userId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*)
             FROM notifications n
             LEFT JOIN notification_reads nr
                ON nr.notification_id = n.id AND nr.user_id = :uid_join
             WHERE n.user_id = :uid AND nr.read_at IS NULL'
        );
        $stmt->execute([
            'uid_join' => $userId,
            'uid' => $userId,
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * @param list<int> $userIds
     * @param array<string, mixed> $announcement
     */
    public function fanOutFromAnnouncement(array $announcement, array $userIds, ?PDO $pdo = null): int
    {
        $db = $pdo ?? $this->pdo;
        $userIds = array_values(array_unique(array_map('intval', $userIds)));
        $userIds = array_filter($userIds, static fn (int $id): bool => $id > 0);

        if ($userIds === []) {
            return 0;
        }

        $announcementId = (int) ($announcement['id'] ?? 0);
        $title = (string) ($announcement['title'] ?? 'Aviso institucional');
        $body = (string) ($announcement['description'] ?? $announcement['content'] ?? '');
        $priority = (string) ($announcement['priority'] ?? 'medium');

        if (!in_array($priority, ['low', 'medium', 'high'], true)) {
            $priority = 'medium';
        }

        $created = 0;
        $exists = $db->prepare(
            'SELECT id FROM notifications
             WHERE user_id = ? AND announcement_id = ? AND type = \'announcement\'
             LIMIT 1'
        );
        $insert = $db->prepare(
            'INSERT INTO notifications
                (user_id, announcement_id, title, body, priority, type, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, \'announcement\', NOW(), NOW())'
        );

        $bodyShort = mb_substr($body, 0, 5000);

        foreach ($userIds as $uid) {
            $exists->execute([$uid, $announcementId]);
            if ($exists->fetchColumn() !== false) {
                continue;
            }

            $insert->execute([$uid, $announcementId, $title, $bodyShort, $priority]);
            $created++;
        }

        return $created;
    }
}
