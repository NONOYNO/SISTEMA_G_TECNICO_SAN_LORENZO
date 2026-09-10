<?php
/** @var array $user */
/** @var list<string> $roles */
/** @var array<string, string> $errors */
$errors = $errors ?? [];
$roles = $roles ?? [];
?>
<div class="card-soft p-4">
    <h1 class="h4 mb-3">Mi perfil</h1>

    <dl class="row mb-4">
        <dt class="col-sm-3">Usuario</dt>
        <dd class="col-sm-9"><?= e((string) ($user['username'] ?? '')) ?></dd>
        <dt class="col-sm-3">Correo</dt>
        <dd class="col-sm-9"><?= e((string) ($user['email'] ?? '')) ?></dd>
        <dt class="col-sm-3">Estado</dt>
        <dd class="col-sm-9"><?= e((string) ($user['status'] ?? '')) ?></dd>
        <dt class="col-sm-3">Roles</dt>
        <dd class="col-sm-9"><?= e($roles === [] ? 'Sin roles' : implode(', ', $roles)) ?></dd>
    </dl>

    <form method="post" action="<?= e(url('/profile')) ?>" novalidate>
        <?= csrf_field() ?>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label for="first_name" class="form-label">Nombres</label>
                <input type="text" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>"
                       id="first_name" name="first_name"
                       value="<?= e((string) ($user['first_name'] ?? '')) ?>" required>
                <?php if (isset($errors['first_name'])): ?>
                    <div class="invalid-feedback"><?= e($errors['first_name']) ?></div>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <label for="last_name" class="form-label">Apellidos</label>
                <input type="text" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>"
                       id="last_name" name="last_name"
                       value="<?= e((string) ($user['last_name'] ?? '')) ?>" required>
                <?php if (isset($errors['last_name'])): ?>
                    <div class="invalid-feedback"><?= e($errors['last_name']) ?></div>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <label for="phone" class="form-label">Teléfono</label>
                <input type="text" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>"
                       id="phone" name="phone"
                       value="<?= e((string) ($user['phone'] ?? '')) ?>">
                <?php if (isset($errors['phone'])): ?>
                    <div class="invalid-feedback"><?= e($errors['phone']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <hr class="my-4">
        <h2 class="h6 mb-3">Cambiar contraseña (opcional)</h2>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label for="current_password" class="form-label">Contraseña actual</label>
                <input type="password" class="form-control <?= isset($errors['current_password']) || isset($errors['password']) ? 'is-invalid' : '' ?>"
                       id="current_password" name="current_password" autocomplete="current-password">
                <?php if (isset($errors['current_password'])): ?>
                    <div class="invalid-feedback"><?= e($errors['current_password']) ?></div>
                <?php elseif (isset($errors['password'])): ?>
                    <div class="invalid-feedback"><?= e($errors['password']) ?></div>
                <?php endif; ?>
            </div>
            <div class="col-md-4">
                <label for="new_password" class="form-label">Nueva contraseña</label>
                <input type="password" class="form-control <?= isset($errors['new_password']) ? 'is-invalid' : '' ?>"
                       id="new_password" name="new_password" autocomplete="new-password">
                <?php if (isset($errors['new_password'])): ?>
                    <div class="invalid-feedback"><?= e($errors['new_password']) ?></div>
                <?php else: ?>
                    <div class="form-text">Mín. 8 caracteres, mayúscula y número.</div>
                <?php endif; ?>
            </div>
            <div class="col-md-4">
                <label for="new_password_confirmation" class="form-label">Confirmar nueva</label>
                <input type="password" class="form-control <?= isset($errors['new_password_confirmation']) ? 'is-invalid' : '' ?>"
                       id="new_password_confirmation" name="new_password_confirmation" autocomplete="new-password">
                <?php if (isset($errors['new_password_confirmation'])): ?>
                    <div class="invalid-feedback"><?= e($errors['new_password_confirmation']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Guardar cambios</button>
    </form>
</div>
