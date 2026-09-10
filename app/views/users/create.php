<?php
/** @var list<array<string,mixed>> $roles */
/** @var array<string,string> $errors */
$errors = $_SESSION['_flash']['errors'] ?? ($errors ?? []);
unset($_SESSION['_flash']['errors']);
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h4 mb-1">Nuevo usuario</h1>
        <p class="text-muted mb-0">Crear cuenta institucional y asignar roles.</p>
    </div>
    <a href="<?= e(url('/users')) ?>" class="btn btn-outline-secondary">Volver</a>
</div>

<div class="card-soft p-4">
    <form method="post" action="<?= e(url('/users')) ?>" autocomplete="off">
        <?= csrf_field() ?>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="first_name">Nombre</label>
                <input type="text" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>"
                       id="first_name" name="first_name" required maxlength="100"
                       value="<?= e((string) old('first_name')) ?>">
                <?php if (isset($errors['first_name'])): ?>
                    <div class="invalid-feedback"><?= e($errors['first_name']) ?></div>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="last_name">Apellido</label>
                <input type="text" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>"
                       id="last_name" name="last_name" required maxlength="100"
                       value="<?= e((string) old('last_name')) ?>">
                <?php if (isset($errors['last_name'])): ?>
                    <div class="invalid-feedback"><?= e($errors['last_name']) ?></div>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="username">Usuario</label>
                <input type="text" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                       id="username" name="username" required maxlength="50"
                       value="<?= e((string) old('username')) ?>">
                <?php if (isset($errors['username'])): ?>
                    <div class="invalid-feedback"><?= e($errors['username']) ?></div>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="email">Correo</label>
                <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                       id="email" name="email" required maxlength="150"
                       value="<?= e((string) old('email')) ?>">
                <?php if (isset($errors['email'])): ?>
                    <div class="invalid-feedback"><?= e($errors['email']) ?></div>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="phone">Teléfono</label>
                <input type="text" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>"
                       id="phone" name="phone" maxlength="30"
                       value="<?= e((string) old('phone')) ?>">
                <?php if (isset($errors['phone'])): ?>
                    <div class="invalid-feedback"><?= e($errors['phone']) ?></div>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="status">Estado</label>
                <select class="form-select <?= isset($errors['status']) ? 'is-invalid' : '' ?>" id="status" name="status">
                    <?php foreach (['active' => 'Activo', 'pending' => 'Pendiente', 'inactive' => 'Inactivo'] as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= (string) old('status', 'active') === $value ? 'selected' : '' ?>>
                            <?= e($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['status'])): ?>
                    <div class="invalid-feedback"><?= e($errors['status']) ?></div>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="password">Contraseña</label>
                <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                       id="password" name="password" required minlength="8" autocomplete="new-password">
                <?php if (isset($errors['password'])): ?>
                    <div class="invalid-feedback"><?= e($errors['password']) ?></div>
                <?php else: ?>
                    <div class="form-text">Mínimo 8 caracteres.</div>
                <?php endif; ?>
            </div>
            <div class="col-12">
                <label class="form-label d-block">Roles</label>
                <?php
                $oldRoles = old('role_ids', []);
                if (!is_array($oldRoles)) {
                    $oldRoles = [];
                }
                $oldRoles = array_map('intval', $oldRoles);
                ?>
                <div class="row g-2">
                    <?php foreach ($roles as $role): ?>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="role_ids[]" value="<?= (int) $role['id'] ?>"
                                       id="role_<?= (int) $role['id'] ?>"
                                    <?= in_array((int) $role['id'], $oldRoles, true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="role_<?= (int) $role['id'] ?>">
                                    <?= e((string) $role['display_name']) ?>
                                    <span class="text-muted small">(<?= e((string) $role['name']) ?>)</span>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (isset($errors['role_ids'])): ?>
                    <div class="text-danger small mt-1"><?= e($errors['role_ids']) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="<?= e(url('/users')) ?>" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>
