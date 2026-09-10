<div class="text-center py-4">
    <h1 class="display-6 text-brand">404</h1>
    <p class="text-muted mb-4"><?= e($message ?? 'La página solicitada no existe.') ?></p>
    <a class="btn btn-primary" href="<?= e(url(auth_check() ? '/dashboard' : '/login')) ?>">Volver</a>
</div>
