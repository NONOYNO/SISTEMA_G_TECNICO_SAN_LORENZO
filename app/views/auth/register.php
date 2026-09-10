<?php /** @var array<string, string> $errors */ ?>
<form method="post" action="<?= e(url('/register')) ?>" novalidate>
    <?= csrf_field() ?>
    <h2 class="h5 mb-3 text-center">Registro</h2>
    <p class="text-muted small">Complete el formulario. Su cuenta se activará con el perfil docente e iniciará sesión de inmediato.</p>

    <div class="row g-2">
        <div class="col-md-6 mb-3">
            <label for="first_name" class="form-label">Nombres</label>
            <input type="text" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>"
                   id="first_name" name="first_name" value="<?= e((string) old('first_name')) ?>" required>
            <?php if (isset($errors['first_name'])): ?>
                <div class="invalid-feedback"><?= e($errors['first_name']) ?></div>
            <?php endif; ?>
        </div>
        <div class="col-md-6 mb-3">
            <label for="last_name" class="form-label">Apellidos</label>
            <input type="text" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>"
                   id="last_name" name="last_name" value="<?= e((string) old('last_name')) ?>" required>
            <?php if (isset($errors['last_name'])): ?>
                <div class="invalid-feedback"><?= e($errors['last_name']) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Correo</label>
        <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
               id="email" name="email" value="<?= e((string) old('email')) ?>" required>
        <?php if (isset($errors['email'])): ?>
            <div class="invalid-feedback"><?= e($errors['email']) ?></div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label for="username" class="form-label">Usuario</label>
        <input type="text" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
               id="username" name="username" value="<?= e((string) old('username')) ?>" required>
        <?php if (isset($errors['username'])): ?>
            <div class="invalid-feedback"><?= e($errors['username']) ?></div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Contraseña</label>
        <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
               id="password" name="password" autocomplete="new-password" required>
        <?php if (isset($errors['password'])): ?>
            <div class="invalid-feedback"><?= e($errors['password']) ?></div>
        <?php else: ?>
            <div class="form-text">Mínimo 8 caracteres, una mayúscula y un número.</div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
        <input type="password" class="form-control <?= isset($errors['password_confirmation']) ? 'is-invalid' : '' ?>"
               id="password_confirmation" name="password_confirmation" autocomplete="new-password" required>
        <?php if (isset($errors['password_confirmation'])): ?>
            <div class="invalid-feedback"><?= e($errors['password_confirmation']) ?></div>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary w-100">Crear cuenta e ingresar</button>
    <p class="text-center small mt-3 mb-0">
        <a href="<?= e(url('/login')) ?>">Volver al login</a>
    </p>
</form>
