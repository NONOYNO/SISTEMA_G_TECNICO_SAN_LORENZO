<div class="text-center py-4">
    <h1 class="display-6 text-brand">403</h1>
    <p class="text-muted mb-4"><?= e($message ?? 'No tiene permiso para acceder a este recurso.') ?></p>
    <a class="btn btn-primary" href="<?= e(url(auth_check() ? '/dashboard' : '/login')) ?>">Volver</a>
</div>
