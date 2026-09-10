<?php
/** @var list<array<string,mixed>> $announcements */
/** @var bool $canManage */
/** @var bool $canCreate */
/** @var bool $canPublish */
/** @var bool $canDelete */
/** @var string|null $statusFilter */

$canDelete = !empty($canDelete);
$canManage = !empty($canManage);
$statusFilter = isset($statusFilter) && is_string($statusFilter) && $statusFilter !== ''
    ? $statusFilter
    : null;

$priorityBadge = static function (string $p): string {
    return match ($p) {
        'high' => 'danger',
        'low' => 'secondary',
        default => 'primary',
    };
};

$statusBadge = static function (string $s): string {
    return match ($s) {
        'published' => 'success',
        'archived' => 'secondary',
        default => 'warning',
    };
};

$statusLabel = static function (string $s): string {
    return match ($s) {
        'published' => 'Publicado',
        'archived' => 'Archivado',
        default => 'Borrador',
    };
};

$priorityLabel = static function (string $p): string {
    return match ($p) {
        'high' => 'Alta',
        'low' => 'Baja',
        default => 'Media',
    };
};

$formatDate = static function (array $row): string {
    $raw = (string) ($row['sort_at'] ?? $row['publish_at'] ?? $row['updated_at'] ?? $row['created_at'] ?? '');
    if ($raw === '') {
        return 'Sin fecha';
    }
    $ts = strtotime($raw);

    return $ts === false ? $raw : date('d/m/Y H:i', $ts);
};
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-1">Avisos institucionales</h1>
        <p class="text-muted mb-0">
            <?= $canManage
                ? 'Gestione y consulte todos los avisos. Los más recientes aparecen primero.'
                : 'Avisos vigentes dirigidos a usted. El más reciente aparece primero.' ?>
        </p>
    </div>
    <?php if (!empty($canCreate)): ?>
        <a href="<?= e(url('/announcements/create')) ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Nuevo aviso
        </a>
    <?php endif; ?>
</div>

<?php if ($canManage): ?>
    <div class="d-flex flex-wrap gap-2 mb-3">
        <?php
        $filters = [
            '' => 'Todos',
            'published' => 'Publicados',
            'draft' => 'Borradores',
            'archived' => 'Archivados',
        ];
        foreach ($filters as $value => $label):
            $active = ($statusFilter === null && $value === '') || $statusFilter === $value;
            $href = $value === '' ? url('/announcements') : url('/announcements?status=' . urlencode($value));
            ?>
            <a href="<?= e($href) ?>"
               class="btn btn-sm <?= $active ? 'btn-primary' : 'btn-outline-primary' ?>">
                <?= e($label) ?>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="mb-3 small text-muted">
    <?= count($announcements) ?> aviso<?= count($announcements) === 1 ? '' : 's' ?>
    · orden: más reciente arriba
</div>

<?php if ($announcements === []): ?>
    <div class="card-soft p-4 text-center text-muted">No hay avisos para mostrar.</div>
<?php else: ?>
    <div class="announcements-feed d-flex flex-column gap-3">
        <?php foreach ($announcements as $row): ?>
            <?php
            $titleSafe = (string) ($row['title'] ?? '');
            $status = (string) ($row['status'] ?? 'draft');
            $prio = (string) ($row['priority'] ?? 'medium');
            $desc = trim((string) ($row['description'] ?? ''));
            if ($desc === '') {
                $desc = mb_substr(trim((string) ($row['content'] ?? '')), 0, 160);
            }
            ?>
            <article class="card-soft announcement-feed-item p-3 p-md-4">
                <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="badge text-bg-<?= e($statusBadge($status)) ?>"><?= e($statusLabel($status)) ?></span>
                        <span class="badge text-bg-<?= e($priorityBadge($prio)) ?>"><?= e($priorityLabel($prio)) ?></span>
                        <?php if (!empty($row['category'])): ?>
                            <span class="badge text-bg-light text-dark border"><?= e((string) $row['category']) ?></span>
                        <?php endif; ?>
                        <span class="badge text-bg-light text-dark border">
                            <?= e((string) (($row['audience'] ?? '') !== '' ? $row['audience'] : 'ALL')) ?>
                        </span>
                    </div>
                    <time class="small text-muted" datetime="<?= e((string) ($row['sort_at'] ?? $row['publish_at'] ?? $row['created_at'] ?? '')) ?>">
                        <i class="bi bi-clock me-1" aria-hidden="true"></i><?= e($formatDate($row)) ?>
                    </time>
                </div>

                <h2 class="h5 mb-2">
                    <a href="<?= e(url('/announcements/' . $row['id'])) ?>" class="text-decoration-none text-reset">
                        <?= e($titleSafe) ?>
                    </a>
                </h2>

                <?php if ($desc !== ''): ?>
                    <p class="mb-3 text-muted"><?= e($desc) ?><?= mb_strlen($desc) >= 160 ? '…' : '' ?></p>
                <?php endif; ?>

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <span class="small text-muted">
                        <?php if (!empty($row['author_name'])): ?>
                            Por <?= e((string) $row['author_name']) ?>
                        <?php endif; ?>
                    </span>
                    <div class="d-flex flex-wrap gap-2">
                        <a class="btn btn-sm btn-outline-primary" href="<?= e(url('/announcements/' . $row['id'])) ?>">Ver</a>
                        <?php if ($canManage && auth_can('announcements.edit')): ?>
                            <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('/announcements/' . $row['id'] . '/edit')) ?>">Editar</a>
                        <?php endif; ?>
                        <?php if ($canManage && !empty($canPublish) && $status !== 'published'): ?>
                            <form method="post" action="<?= e(url('/announcements/' . $row['id'] . '/publish')) ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-success">Publicar</button>
                            </form>
                        <?php endif; ?>
                        <?php if ($canManage && $canDelete): ?>
                            <form method="post" action="<?= e(url('/announcements/' . $row['id'] . '/delete')) ?>" class="d-inline"
                                  onsubmit="return confirm('¿Eliminar el aviso «<?= e(addslashes($titleSafe)) ?>»?\n\nEsta acción no se puede deshacer.');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar aviso">
                                    <i class="bi bi-trash" aria-hidden="true"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
