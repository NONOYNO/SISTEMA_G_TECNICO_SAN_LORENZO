<?php
/** @var array<string,mixed> $announcement */
/** @var list<array<string,mixed>> $attachments */
/** @var bool $canEdit */
/** @var bool $canDelete */
/** @var bool $canPublish */

$priorityBadge = match ((string) ($announcement['priority'] ?? 'medium')) {
    'high' => 'danger',
    'low' => 'secondary',
    default => 'primary',
};
$priorityLabel = match ((string) ($announcement['priority'] ?? 'medium')) {
    'high' => 'Alta',
    'low' => 'Baja',
    default => 'Media',
};
$statusBadge = match ((string) ($announcement['status'] ?? 'draft')) {
    'published' => 'success',
    'archived' => 'secondary',
    default => 'warning',
};
$statusLabel = match ((string) ($announcement['status'] ?? 'draft')) {
    'published' => 'Publicado',
    'archived' => 'Archivado',
    default => 'Borrador',
};
?>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
    <div>
        <h1 class="h3 mb-2"><?= e((string) $announcement['title']) ?></h1>
        <div class="d-flex flex-wrap gap-2">
            <span class="badge text-bg-<?= e($statusBadge) ?>"><?= e($statusLabel) ?></span>
            <span class="badge text-bg-<?= e($priorityBadge) ?>"><?= e($priorityLabel) ?></span>
            <?php if (!empty($announcement['category'])): ?>
                <span class="badge text-bg-light text-dark border"><?= e((string) $announcement['category']) ?></span>
            <?php endif; ?>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= e(url('/announcements')) ?>" class="btn btn-outline-secondary btn-sm">Volver</a>
        <?php if (!empty($canEdit)): ?>
            <a href="<?= e(url('/announcements/' . $announcement['id'] . '/edit')) ?>" class="btn btn-outline-primary btn-sm">Editar</a>
        <?php endif; ?>
        <?php if (!empty($canPublish) && ($announcement['status'] ?? '') !== 'published'): ?>
            <form method="post" action="<?= e(url('/announcements/' . $announcement['id'] . '/publish')) ?>" class="d-inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-success btn-sm">Publicar</button>
            </form>
        <?php endif; ?>
        <?php if (!empty($canPublish) && ($announcement['status'] ?? '') === 'published'): ?>
            <form method="post" action="<?= e(url('/announcements/' . $announcement['id'] . '/archive')) ?>" class="d-inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-secondary btn-sm">Archivar</button>
            </form>
        <?php endif; ?>
        <?php if (!empty($canDelete)): ?>
            <form method="post" action="<?= e(url('/announcements/' . $announcement['id'] . '/delete')) ?>" class="d-inline"
                  onsubmit="return confirm('¿Eliminar el aviso «<?= e(addslashes((string) $announcement['title'])) ?>»?\n\nEsta acción no se puede deshacer. Se borrarán también adjuntos y notificaciones asociadas.');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-trash me-1"></i>Eliminar
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="card-soft p-4 mb-3">
    <?php if (!empty($announcement['description'])): ?>
        <p class="lead"><?= e((string) $announcement['description']) ?></p>
    <?php endif; ?>
    <div class="announcement-body" style="white-space: pre-wrap;"><?= e((string) $announcement['content']) ?></div>
    <hr>
    <dl class="row small mb-0">
        <dt class="col-sm-3">Autor</dt>
        <dd class="col-sm-9"><?= e((string) ($announcement['author_name'] ?? '')) ?></dd>
        <dt class="col-sm-3">Audiencia</dt>
        <dd class="col-sm-9"><?= e((string) ($announcement['audience'] ?: 'ALL')) ?></dd>
        <dt class="col-sm-3">Publicación</dt>
        <dd class="col-sm-9"><?= e((string) ($announcement['publish_at'] ?? '—')) ?></dd>
        <dt class="col-sm-3">Expira</dt>
        <dd class="col-sm-9"><?= e((string) ($announcement['expire_at'] ?? '—')) ?></dd>
    </dl>
</div>

<?php if ($attachments !== []): ?>
<div class="card-soft p-3">
    <h2 class="h6 mb-3">Archivos adjuntos</h2>
    <ul class="list-group list-group-flush">
        <?php foreach ($attachments as $file): ?>
            <?php
            $bytes = (int) ($file['size_bytes'] ?? 0);
            $sizeLabel = $bytes >= 1048576
                ? number_format($bytes / 1048576, 1) . ' MB'
                : number_format(max(1, $bytes / 1024), 0) . ' KB';
            ?>
            <li class="list-group-item px-0 d-flex justify-content-between align-items-center gap-2">
                <span>
                    <i class="bi bi-paperclip me-2"></i><?= e((string) $file['original_name']) ?>
                    <span class="text-muted small">(<?= e($sizeLabel) ?>)</span>
                </span>
                <?php if (auth_can('files.download')): ?>
                    <a class="btn btn-sm btn-outline-primary" href="<?= e(url('/files/' . $file['id'] . '/download')) ?>">Descargar</a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>
