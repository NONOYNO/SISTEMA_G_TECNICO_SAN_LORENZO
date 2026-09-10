<?php
/** @var list<array<string,mixed>> $announcements */
/** @var bool $canManage */
/** @var bool $canCreate */
/** @var bool $canPublish */
/** @var bool $canDelete */

$canDelete = !empty($canDelete);

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
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1">Avisos institucionales</h1>
        <p class="text-muted mb-0">Publicaciones del vicerrectorado y administración.</p>
    </div>
    <?php if (!empty($canCreate)): ?>
        <a href="<?= e(url('/announcements/create')) ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Nuevo aviso
        </a>
    <?php endif; ?>
</div>

<?php if ($announcements === []): ?>
    <div class="card-soft p-4 text-center text-muted">No hay avisos para mostrar.</div>
<?php elseif (!empty($canManage)): ?>
    <div class="card-soft p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Título</th>
                        <th>Estado</th>
                        <th>Prioridad</th>
                        <th>Audiencia</th>
                        <th>Fecha</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($announcements as $row): ?>
                    <?php $titleSafe = (string) $row['title']; ?>
                    <tr>
                        <td>
                            <a href="<?= e(url('/announcements/' . $row['id'])) ?>" class="fw-semibold text-decoration-none">
                                <?= e($titleSafe) ?>
                            </a>
                            <?php if (!empty($row['category'])): ?>
                                <div class="small text-muted"><?= e((string) $row['category']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge text-bg-<?= e($statusBadge((string) $row['status'])) ?>">
                                <?= e($statusLabel((string) $row['status'])) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge text-bg-<?= e($priorityBadge((string) $row['priority'])) ?>">
                                <?= e($priorityLabel((string) $row['priority'])) ?>
                            </span>
                        </td>
                        <td class="small"><?= e((string) ($row['audience'] ?: 'ALL')) ?></td>
                        <td class="small text-muted">
                            <?= e((string) ($row['publish_at'] ?? $row['created_at'] ?? '')) ?>
                        </td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-outline-primary" href="<?= e(url('/announcements/' . $row['id'])) ?>">Ver</a>
                            <?php if (auth_can('announcements.edit')): ?>
                                <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('/announcements/' . $row['id'] . '/edit')) ?>">Editar</a>
                            <?php endif; ?>
                            <?php if ($canDelete): ?>
                                <form method="post" action="<?= e(url('/announcements/' . $row['id'] . '/delete')) ?>" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar el aviso «<?= e(addslashes($titleSafe)) ?>»?\n\nEsta acción no se puede deshacer. Se borrarán también adjuntos y notificaciones asociadas.');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar aviso">
                                        <i class="bi bi-trash me-1"></i>Eliminar
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($announcements as $row): ?>
            <div class="col-md-6">
                <div class="card-soft p-3 h-100">
                    <div class="d-flex justify-content-between gap-2 mb-2">
                        <h2 class="h6 mb-0"><?= e((string) $row['title']) ?></h2>
                        <span class="badge text-bg-<?= e($priorityBadge((string) $row['priority'])) ?>">
                            <?= e($priorityLabel((string) $row['priority'])) ?>
                        </span>
                    </div>
                    <p class="small text-muted mb-2"><?= e((string) ($row['description'] ?? '')) ?></p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted"><?= e((string) ($row['publish_at'] ?? '')) ?></span>
                        <a href="<?= e(url('/announcements/' . $row['id'])) ?>" class="btn btn-sm btn-outline-primary">Ver</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
