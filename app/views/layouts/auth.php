<?php
/** @var string $content */
/** @var string $title */
$pageTitle = ($title ?? 'Acceso') . ' · ' . (string) config('app.name', 'Portal UE San Lorenzo');
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
<body class="auth-body">
    <div class="auth-card">
        <div class="text-center mb-4">
            <img class="auth-shield" src="<?= e(asset('img/escudo-san-lorenzo.png')) ?>" alt="Escudo Unidad Educativa Fiscomisional San Lorenzo" width="86" height="86">
            <div class="school-name">UE Fiscomisional San Lorenzo</div>
            <div class="text-muted small">Portal informativo-administrativo</div>
        </div>
        <?php require app_path('views/partials/alerts.php'); ?>
        <?= $content ?>
    </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(asset('js/app.js')) ?>"></script>
</body>
</html>
