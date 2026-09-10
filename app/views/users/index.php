<?php
/** @var list<array<string,mixed>> $users */
/** @var array{items:list,total:int,page:int,per_page:int,pages:int} $pagination */
/** @var list<array<string,mixed>> $roles */
/** @var array{q:string,status:string,role:string} $filters */

$statusLabels = [
    'active' => 'Activo',
    'inactive' => 'Inactivo',
    'pending' => 'Pendiente',
];
$statusBadges = [
    'active' => 'success',
    'inactive' => 'secondary',
    'pending' => 'warning',
];
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h4 mb-1">Usuarios</h1>
        <p class="text-muted mb-0">Gestión de cuentas institucionales.</p>
    </div>
    <?php if (auth_can('users.create')): ?>
        <a href="<?= e(url('/users/create')) ?>" class="btn btn-primary">
            <i class="bi bi-person-plus me-1"></i> Nuevo usuario
        </a>
    <?php endif; ?>
</div>

<div class="card-soft p-3 mb-4">
    <form method="get" action="<?= e(url('/users')) ?>" class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label" for="q">Buscar</label>
            <input type="search" class="form-control" id="q" name="q"
                   value="<?= e($filters['q'] ?? '') ?>"
                   placeholder="Nombre, usuario o correo">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="status">Estado</label>
            <select class="form-select" id="status" name="status">
                <option value="">Todos</option>
                <?php foreach ($statusLabels as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= ($filters['status'] ?? '') === $value ? 'selected' : '' ?>>
                        <?= e($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="role">Rol</label>
            <select class="form-select" id="role" name="role">
                <option value="">Todos</option>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= e((string) $role['id']) ?>"
                        <?= (string) ($filters['role'] ?? '') === (string) $role['id'] ? 'selected' : '' ?>>
                        <?= e((string) $role['display_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 d-grid">
            <button type="submit" class="btn btn-outline-primary">Filtrar</button>
        </div>
    </form>
</div>

<div class="card-soft p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th>Nombre</th>
                <th>Usuario</th>
                <th>Correo</th>
                <th>Roles</th>
                <th>Estado</th>
                <th class="text-end">Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($users === []): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No se encontraron usuarios.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <?php
                    $status = (string) ($user['status'] ?? '');
                    $badge = $statusBadges[$status] ?? 'secondary';
                    $statusLabel = $statusLabels[$status] ?? $status;
                    ?>
                    <tr>
                        <td>
                            <strong><?= e(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))) ?></strong>
                        </td>
                        <td><?= e((string) ($user['username'] ?? '')) ?></td>
                        <td><?= e((string) ($user['email'] ?? '')) ?></td>
                        <td>
                            <?php foreach (($user['roles'] ?? []) as $role): ?>
                                <span class="badge text-bg-light border me-1"><?= e((string) $role['display_name']) ?></span>
                            <?php endforeach; ?>
                            <?php if (empty($user['roles'])): ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge text-bg-<?= e($badge) ?>"><?= e($statusLabel) ?></span></td>
                        <td class="text-end text-nowrap">
                            <?php if (auth_can('users.edit')): ?>
                                <a href="<?= e(url('/users/' . $user['id'] . '/edit')) ?>"
                                   class="btn btn-sm btn-outline-primary">Editar</a>
                            <?php endif; ?>
                            <?php if (auth_can('users.delete') && $status !== 'inactive'): ?>
                                <form method="post" action="<?= e(url('/users/' . $user['id'] . '/delete')) ?>"
                                      class="d-inline"
                                      onsubmit="return confirm('¿Desactivar este usuario?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Desactivar</button>
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

<?php if (($pagination['pages'] ?? 1) > 1): ?>
    <nav class="mt-3" aria-label="Paginación usuarios">
        <ul class="pagination justify-content-center">
            <?php
            $query = array_filter([
                'q' => $filters['q'] ?? '',
                'status' => $filters['status'] ?? '',
                'role' => $filters['role'] ?? '',
            ], static fn ($v) => $v !== '' && $v !== null);
            for ($p = 1; $p <= (int) $pagination['pages']; $p++):
                $query['page'] = $p;
                $href = url('/users?' . http_build_query($query));
                ?>
                <li class="page-item <?= $p === (int) $pagination['page'] ? 'active' : '' ?>">
                    <a class="page-link" href="<?= e($href) ?>"><?= $p ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>
