<form method="post" action="<?= e(url('/login')) ?>" novalidate>
    <?= csrf_field() ?>
    <h2 class="h5 mb-3 text-center">Iniciar sesión</h2>
    <div class="mb-3">
        <label for="login" class="form-label">Usuario o correo</label>
        <input
            type="text"
            class="form-control"
            id="login"
            name="login"
            value="<?= e((string) old('login')) ?>"
            autocomplete="username"
            required
            autofocus
        >
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Contraseña</label>
        <input type="password" class="form-control" id="password" name="password" autocomplete="current-password" required>
    </div>
    <button type="submit" class="btn btn-primary w-100">Entrar</button>
    <p class="text-center small mt-3 mb-0">
        ¿No tiene cuenta?
        <a href="<?= e(url('/register')) ?>">Registrarse</a>
    </p>
</form>
