<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Database;
use PDO;
use Throwable;

final class AuditService
{
    private PDO $pdo;

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
                    TRIM(CONCAT(COALESCE(u.first_name, \'\'), \' \', COALESCE(u.last_name, \'\'))) AS user_name
             FROM audit_logs a
             LEFT JOIN users u ON u.id = a.user_id
             ORDER BY a.created_at DESC, a.id DESC
             LIMIT ' . $limit
        );
        $stmt->execute();

        /** @var list<array<string, mixed>> $rows */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $rows;
    }
}
