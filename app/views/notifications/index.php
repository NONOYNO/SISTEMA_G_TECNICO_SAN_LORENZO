<?php
/** @var list<array<string,mixed>> $notifications */
/** @var int $unreadCount */

$priorityBadge = static function (string $p): string {
    return match ($p) {
        'high' => 'danger',
        'low' => 'secondary',
        default => 'primary',
    };
};

$priorityLabel = static function (string $p): string {
    return match ($p) {
        'high' => 'Alta',
        'low' => 'Baja',
        default => 'Media',
    };
};
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1">Notificaciones</h1>
        <p class="text-muted mb-0">
            Centro de notificaciones
            <?php if ($unreadCount > 0): ?>
                · <span class="badge text-bg-danger" id="unread-badge"><?= (int) $unreadCount ?> sin leer</span>
            <?php else: ?>
                · <span class="text-success small" id="unread-badge">Todo al día</span>
            <?php endif; ?>
        </p>
    </div>
</div>

<?php if ($notifications === []): ?>
    <div class="card-soft p-4 text-center text-muted">No hay notificaciones.</div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($notifications as $n): ?>
            <?php
            $isRead = !empty($n['is_read']);
            $prio = (string) ($n['priority'] ?? 'medium');
            ?>
            <div class="col-12" data-notification-card="<?= (int) $n['id'] ?>">
                <div class="card-soft p-3 <?= $isRead ? '' : 'border-start border-4 border-primary' ?>">
                    <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                        <h2 class="h6 mb-0">
                            <?= e((string) $n['title']) ?>
                            <?php if (!$isRead): ?>
                                <span class="badge text-bg-info ms-1 mark-unread-label">Nueva</span>
                            <?php endif; ?>
                        </h2>
                        <span class="badge text-bg-<?= e($priorityBadge($prio)) ?>"><?= e($priorityLabel($prio)) ?></span>
                    </div>
                    <p class="mb-2 text-muted"><?= e((string) ($n['body'] ?? '')) ?></p>
                    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                        <span class="small text-muted"><?= e((string) ($n['created_at'] ?? '')) ?></span>
                        <div class="d-flex flex-wrap gap-2">
                            <?php if (!empty($n['announcement_id'])): ?>
                                <a class="btn btn-sm btn-outline-primary" href="<?= e(url('/announcements/' . $n['announcement_id'])) ?>">Ver aviso</a>
                            <?php endif; ?>
                            <?php if (!empty($n['attachments']) && auth_can('files.download')): ?>
                                <?php foreach ($n['attachments'] as $file): ?>
                                    <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('/files/' . $file['id'] . '/download')) ?>">
                                        <i class="bi bi-download me-1"></i><?= e((string) $file['original_name']) ?>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <?php if (!$isRead): ?>
                                <button type="button"
                                        class="btn btn-sm btn-success btn-mark-read"
                                        data-id="<?= (int) $n['id'] ?>"
                                        data-url="<?= e(url('/notifications/' . $n['id'] . '/read')) ?>">
                                    Marcar leída
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
