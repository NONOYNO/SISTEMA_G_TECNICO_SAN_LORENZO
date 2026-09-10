<?php
/** @var list<array<string,mixed>> $roles */
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h4 mb-1">Roles y permisos</h1>
        <p class="text-muted mb-0">Roles institucionales y matriz de acceso.</p>
    </div>
    <?php if (auth_can('roles.create')): ?>
        <a href="<?= e(url('/roles/create')) ?>" class="btn btn-primary">
            <i class="bi bi-shield-plus me-1"></i> Nuevo rol
        </a>
    <?php endif; ?>
</div>

<div class="card-soft p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th>Rol</th>
                <th>Código</th>
                <th>Descripción</th>
                <th>Usuarios</th>
                <th>Permisos</th>
                <th>Tipo</th>
                <th class="text-end">Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($roles === []): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No hay roles registrados.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($roles as $role): ?>
                    <tr>
                        <td><strong><?= e((string) $role['display_name']) ?></strong></td>
                        <td><code><?= e((string) $role['name']) ?></code></td>
                        <td class="text-muted"><?= e((string) ($role['description'] ?? '—')) ?></td>
                        <td><?= (int) ($role['users_count'] ?? 0) ?></td>
                        <td><?= (int) ($role['permissions_count'] ?? 0) ?></td>
                        <td>
                            <?php if ((int) ($role['is_system'] ?? 0) === 1): ?>
                                <span class="badge text-bg-info">Sistema</span>
                            <?php else: ?>
                                <span class="badge text-bg-light border">Personalizado</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end text-nowrap">
                            <?php if (auth_can('roles.edit')): ?>
                                <a href="<?= e(url('/roles/' . $role['id'] . '/edit')) ?>"
                                   class="btn btn-sm btn-outline-primary">Editar</a>
                            <?php endif; ?>
                            <?php if (auth_can('roles.delete') && (int) ($role['is_system'] ?? 0) !== 1): ?>
                                <form method="post" action="<?= e(url('/roles/' . $role['id'] . '/delete')) ?>"
                                      class="d-inline"
                                      onsubmit="return confirm('¿Eliminar este rol?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
