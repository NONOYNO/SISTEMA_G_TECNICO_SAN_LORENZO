<?php
/** @var array<string, list<array<string,mixed>>> $permissionsGrouped */
/** @var list<int> $selectedPermissions */
/** @var array<string,string> $errors */
$errors = $_SESSION['_flash']['errors'] ?? ($errors ?? []);
unset($_SESSION['_flash']['errors']);

$selected = old('permission_ids', $selectedPermissions);
if (!is_array($selected)) {
    $selected = [];
}
$selected = array_map('intval', $selected);
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h4 mb-1">Nuevo rol</h1>
        <p class="text-muted mb-0">Definir rol y permisos asociados.</p>
    </div>
    <a href="<?= e(url('/roles')) ?>" class="btn btn-outline-secondary">Volver</a>
</div>

<div class="card-soft p-4">
    <form method="post" action="<?= e(url('/roles')) ?>">
        <?= csrf_field() ?>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label" for="name">Código</label>
                <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                       id="name" name="name" required maxlength="50"
                       placeholder="EJ: COORDINADOR"
                       value="<?= e((string) old('name')) ?>">
                <?php if (isset($errors['name'])): ?>
                    <div class="invalid-feedback"><?= e($errors['name']) ?></div>
                <?php else: ?>
                    <div class="form-text">MAYÚSCULAS, números y guion bajo.</div>
                <?php endif; ?>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="display_name">Nombre visible</label>
                <input type="text" class="form-control <?= isset($errors['display_name']) ? 'is-invalid' : '' ?>"
                       id="display_name" name="display_name" required maxlength="100"
                       value="<?= e((string) old('display_name')) ?>">
                <?php if (isset($errors['display_name'])): ?>
                    <div class="invalid-feedback"><?= e($errors['display_name']) ?></div>
                <?php endif; ?>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="description">Descripción</label>
                <input type="text" class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>"
                       id="description" name="description" maxlength="255"
                       value="<?= e((string) old('description')) ?>">
                <?php if (isset($errors['description'])): ?>
                    <div class="invalid-feedback"><?= e($errors['description']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <h2 class="h6 mb-3">Permisos</h2>
        <?php if ($permissionsGrouped === []): ?>
            <p class="text-muted">No hay permisos en el catálogo. Ejecute los seeders.</p>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($permissionsGrouped as $group => $permissions): ?>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <strong class="text-uppercase small text-muted"><?= e((string) $group) ?></strong>
                            <div class="mt-2">
                                <?php foreach ($permissions as $permission): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               name="permission_ids[]"
                                               value="<?= (int) $permission['id'] ?>"
                                               id="perm_<?= (int) $permission['id'] ?>"
                                            <?= in_array((int) $permission['id'], $selected, true) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="perm_<?= (int) $permission['id'] ?>">
                                            <code><?= e((string) $permission['name']) ?></code>
                                            <?php if (!empty($permission['description'])): ?>
                                                <span class="text-muted small">— <?= e((string) $permission['description']) ?></span>
                                            <?php endif; ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="<?= e(url('/roles')) ?>" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>
