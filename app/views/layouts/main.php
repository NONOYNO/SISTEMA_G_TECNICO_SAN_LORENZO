<?php
/** @var string $content */
/** @var string $title */
$pageTitle = ($title ?? 'Portal') . ' · ' . (string) config('app.name', 'Portal UE San Lorenzo');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= e(asset('css/app.css')) ?>" rel="stylesheet" id="portal-theme">
</head>
<body>
<div class="app-shell">
    <?php require app_path('views/partials/sidebar.php'); ?>
    <div class="app-main">
        <?php require app_path('views/partials/navbar.php'); ?>
        <main class="app-content">
            <?php require app_path('views/partials/alerts.php'); ?>
            <?= $content ?>
        </main>
    </div>
</div>
<div class="modal fade" id="soporteModal" tabindex="-1" aria-labelledby="soporteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="soporteModalLabel">Soporte institucional</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Para asistencia técnica del Portal UE San Lorenzo, contacte a la administración del plantel.</p>
                <p class="mb-0 text-muted small">Use Mi perfil para actualizar sus datos de cuenta. No se modifican sesiones ni permisos desde esta ventana.</p>
            </div>
            <div class="modal-footer">
                <a class="btn btn-outline-primary" href="<?= e(url('/profile')) ?>">Ir a mi perfil</a>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(asset('js/app.js')) ?>"></script>
</body>
</html>
