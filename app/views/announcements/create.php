<?php
/** @var array<string,mixed>|null $announcement */
/** @var bool $canPublish */
$a = $announcement ?? [];
$isEdit = !empty($a['id']);
$action = $isEdit ? url('/announcements/' . $a['id']) : url('/announcements');
$titleValue = (string) old('title', $a['title'] ?? '');
$descValue = (string) old('description', $a['description'] ?? '');
$contentValue = (string) old('content', $a['content'] ?? '');
$categoryValue = (string) old('category', $a['category'] ?? '');
$priorityValue = (string) old('priority', $a['priority'] ?? 'medium');
$statusValue = (string) old('status', $a['status'] ?? 'draft');
$audienceValue = (string) old('audience', $a['audience'] ?? 'DOCENTE');
$expireValue = (string) old('expire_at', isset($a['expire_at']) && $a['expire_at'] ? date('Y-m-d\TH:i', strtotime((string) $a['expire_at'])) : '');
$selectedAudience = array_filter(array_map('trim', explode(',', strtoupper($audienceValue))));
?>
<div class="mb-4">
    <h1 class="h3 mb-1"><?= $isEdit ? 'Editar aviso' : 'Nuevo aviso' ?></h1>
    <p class="text-muted mb-0">Complete los datos del aviso institucional.</p>
</div>

<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" class="card-soft p-4">
    <?= csrf_field() ?>

    <div class="row g-3">
        <div class="col-12">
            <label class="form-label" for="title">Título *</label>
            <input type="text" class="form-control" id="title" name="title" maxlength="200" required value="<?= e($titleValue) ?>">
        </div>
        <div class="col-12">
            <label class="form-label" for="description">Descripción corta</label>
            <input type="text" class="form-control" id="description" name="description" maxlength="500" value="<?= e($descValue) ?>">
        </div>
        <div class="col-12">
            <label class="form-label" for="content">Contenido *</label>
            <textarea class="form-control" id="content" name="content" rows="8" required><?= e($contentValue) ?></textarea>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="category">Categoría</label>
            <select class="form-select" id="category" name="category">
                <?php foreach (['', 'Académico', 'Administrativo', 'Eventos', 'Urgente'] as $cat): ?>
                    <option value="<?= e($cat) ?>" <?= $categoryValue === $cat ? 'selected' : '' ?>>
                        <?= $cat === '' ? '— Sin categoría —' : e($cat) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="priority">Prioridad</label>
            <select class="form-select" id="priority" name="priority">
                <?php foreach (['low' => 'Baja', 'medium' => 'Media', 'high' => 'Alta'] as $val => $label): ?>
                    <option value="<?= e($val) ?>" <?= $priorityValue === $val ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="status">Estado</label>
            <select class="form-select" id="status" name="status">
                <option value="draft" <?= $statusValue === 'draft' ? 'selected' : '' ?>>Borrador</option>
                <?php if (!empty($canPublish)): ?>
                    <option value="published" <?= $statusValue === 'published' ? 'selected' : '' ?>>Publicado</option>
                    <option value="archived" <?= $statusValue === 'archived' ? 'selected' : '' ?>>Archivado</option>
                <?php endif; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="expire_at">Expira</label>
            <input type="datetime-local" class="form-control" id="expire_at" name="expire_at" value="<?= e($expireValue) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Audiencia (roles)</label>
            <div class="d-flex flex-wrap gap-3">
                <?php foreach (['ALL', 'DOCENTE', 'RECTOR', 'VICERRECTOR', 'ADMIN'] as $role): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="audience[]" value="<?= e($role) ?>"
                               id="aud_<?= e($role) ?>"
                            <?= in_array($role, $selectedAudience, true) || ($role === 'ALL' && in_array('ALL', $selectedAudience, true)) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="aud_<?= e($role) ?>"><?= e($role) ?></label>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="form-text">CSV de roles o ALL. Requerido para publicar.</div>
        </div>
        <?php if (auth_can('files.upload')): ?>
            <?php $maxMb = (int) env('UPLOAD_MAX_MB', '512'); ?>
            <div class="col-12">
                <label class="form-label" for="attachments">Archivos adjuntos (opcional)</label>
                <input
                    type="file"
                    class="form-control"
                    id="attachments"
                    name="attachments[]"
                    multiple
                    data-max-mb="<?= e((string) $maxMb) ?>"
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.zip"
                >
                <div class="form-text">
                    Puede seleccionar <strong>varios archivos</strong>. Máximo <strong><?= e((string) $maxMb) ?> MB</strong> por archivo
                    (permite más de 200 MB). Formatos: PDF, Office, imágenes, ZIP.
                </div>
                <ul id="attachments-preview" class="list-unstyled small mt-2 mb-0 text-muted"></ul>
            </div>
        <?php endif; ?>
    </div>

    <div class="d-flex flex-wrap gap-2 mt-4">
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Guardar cambios' : 'Crear aviso' ?></button>
        <a href="<?= e(url($isEdit ? '/announcements/' . $a['id'] : '/announcements')) ?>" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
