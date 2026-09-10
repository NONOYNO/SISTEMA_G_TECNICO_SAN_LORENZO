<?php
$types = [
    'success' => 'success',
    'error' => 'danger',
    'danger' => 'danger',
    'warning' => 'warning',
    'info' => 'info',
];

foreach ($types as $flashKey => $bootstrapClass):
    $message = flash($flashKey);
    if ($message === null || $message === '') {
        continue;
    }
    ?>
    <div class="alert alert-<?= e($bootstrapClass) ?> alert-dismissible fade show" role="alert">
        <?= e(is_scalar($message) ? (string) $message : json_encode($message, JSON_UNESCAPED_UNICODE)) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endforeach; ?>
