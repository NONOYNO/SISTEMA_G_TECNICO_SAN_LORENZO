<?php /** @var list<array<string, mixed>> $logs */ ?>
<div class="card-soft p-4 mb-4 module-hero">
    <h1 class="h4 mb-2"><?= e($heading ?? 'Auditoría') ?></h1>
    <p class="mb-0 text-muted"><?= e($description ?? 'Registro de auditoría.') ?></p>
</div>

<div class="card-soft p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0 align-middle">
            <thead class="table-light">
            <tr>
                <th scope="col">Fecha</th>
                <th scope="col">Usuario</th>
                <th scope="col">Acción</th>
                <th scope="col">Entidad</th>
                <th scope="col">IP</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">No hay eventos registrados.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($logs as $log): ?>
                    <?php
                    $displayName = trim((string) ($log['user_name'] ?? ''));
                    if ($displayName === '') {
                        $displayName = (string) ($log['username'] ?? '');
                    }
                    if ($displayName === '' && empty($log['user_id'])) {
                        $displayName = '—';
                    }
                    $entity = (string) ($log['entity'] ?? '');
                    $entityId = $log['entity_id'] ?? null;
                    $entityLabel = $entity !== ''
                        ? $entity . ($entityId !== null && $entityId !== '' ? ' #' . $entityId : '')
                        : '—';
                    ?>
                    <tr>
                        <td class="text-nowrap"><?= e((string) ($log['created_at'] ?? '')) ?></td>
                        <td><?= e($displayName) ?></td>
                        <td><code><?= e((string) ($log['action'] ?? '')) ?></code></td>
                        <td><?= e($entityLabel) ?></td>
                        <td class="text-nowrap"><?= e((string) ($log['ip'] ?? '—')) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
