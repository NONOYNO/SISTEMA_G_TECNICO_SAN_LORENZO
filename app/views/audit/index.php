<?php
/** @var list<array<string, mixed>> $logs */
/** @var array{items: list, total: int, page: int, per_page: int, pages: int} $pagination */
/** @var array{total_events: int, total_logins: int, total_announcements: int, total_modifications: int} $statistics */
/** @var list<array{id: int, username: string, name: string}> $filterUsers */
/** @var list<array{action: string, label: string}> $filterActions */
/** @var list<array{entity: string, label: string}> $filterEntities */
/** @var array{q: string, user_id: string, action: string, entity: string, date_from: string, date_to: string} $filters */

$formatDate = static function (string $raw): string {
    if ($raw === '') {
        return '—';
    }
    $ts = strtotime($raw);
    return $ts === false ? $raw : date('d/m/Y H:i:s', $ts);
};

$hasActiveFilters = ($filters['q'] !== '')
    || ($filters['user_id'] !== '')
    || ($filters['action'] !== '')
    || ($filters['entity'] !== '')
    || ($filters['date_from'] !== '')
    || ($filters['date_to'] !== '');
?>

<!-- Encabezado del Módulo -->
<div class="card-soft p-4 mb-4 module-hero">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                    <i class="bi bi-shield-check me-1"></i> Seguridad y Trazabilidad
                </span>
                <?php if ($hasActiveFilters): ?>
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                        <i class="bi bi-funnel me-1"></i> Filtros activos
                    </span>
                <?php endif; ?>
            </div>
            <h1 class="h4 mb-1 text-primary fw-bold"><?= e($heading ?? 'Registro de auditoría') ?></h1>
            <p class="mb-0 text-muted">
                <?= e($description ?? 'Trazabilidad de seguridad, autenticación y operaciones sensibles del sistema.') ?>
            </p>
        </div>
        <div>
            <span class="text-muted small">
                <i class="bi bi-database me-1"></i> Modo sólo lectura · Registros inmutables
            </span>
        </div>
    </div>
</div>

<!-- Tarjetas Estadísticas Superiores -->
<div class="row g-3 mb-4">
    <!-- Total de eventos -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-soft p-3 h-100 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Total de eventos</span>
                    <h2 class="h3 fw-bold mb-0 mt-1 text-primary"><?= number_format($statistics['total_events'] ?? 0) ?></h2>
                </div>
                <div class="p-2 bg-primary-subtle text-primary rounded-3">
                    <i class="bi bi-journal-text fs-4"></i>
                </div>
            </div>
            <div class="small text-muted border-top pt-2 mt-2">
                <i class="bi bi-clock-history me-1"></i> Histórico general registrado
            </div>
        </div>
    </div>

    <!-- Inicios de sesión -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-soft p-3 h-100 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Inicios de sesión</span>
                    <h2 class="h3 fw-bold mb-0 mt-1 text-success"><?= number_format($statistics['total_logins'] ?? 0) ?></h2>
                </div>
                <div class="p-2 bg-success-subtle text-success rounded-3">
                    <i class="bi bi-shield-lock-fill fs-4"></i>
                </div>
            </div>
            <div class="small text-muted border-top pt-2 mt-2">
                <i class="bi bi-check-circle me-1 text-success"></i> Autenticaciones exitosas
            </div>
        </div>
    </div>

    <!-- Avisos creados -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-soft p-3 h-100 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Avisos creados</span>
                    <h2 class="h3 fw-bold mb-0 mt-1 text-info"><?= number_format($statistics['total_announcements'] ?? 0) ?></h2>
                </div>
                <div class="p-2 bg-info-subtle text-info rounded-3">
                    <i class="bi bi-megaphone-fill fs-4"></i>
                </div>
            </div>
            <div class="small text-muted border-top pt-2 mt-2">
                <i class="bi bi-broadcast me-1 text-info"></i> Publicaciones y comunicados
            </div>
        </div>
    </div>

    <!-- Modificaciones -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-soft p-3 h-100 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Modificaciones</span>
                    <h2 class="h3 fw-bold mb-0 mt-1 text-warning-emphasis"><?= number_format($statistics['total_modifications'] ?? 0) ?></h2>
                </div>
                <div class="p-2 bg-warning-subtle text-warning-emphasis rounded-3">
                    <i class="bi bi-pencil-square fs-4"></i>
                </div>
            </div>
            <div class="small text-muted border-top pt-2 mt-2">
                <i class="bi bi-arrow-repeat me-1 text-warning-emphasis"></i> Ediciones y cambios de estado
            </div>
        </div>
    </div>
</div>

<!-- Caja de Búsqueda y Filtros -->
<div class="card-soft p-3 mb-4">
    <form method="get" action="<?= e(url('/audit')) ?>" id="auditFilterForm">
        <!-- Fila 1: Búsqueda libre -->
        <div class="row g-2 mb-3">
            <div class="col-12">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="search"
                           class="form-control border-start-0 ps-0"
                           id="q"
                           name="q"
                           value="<?= e($filters['q']) ?>"
                           placeholder="Buscar por usuario, acción o entidad..."
                           aria-label="Buscar en auditoría">
                    <?php if ($filters['q'] !== ''): ?>
                        <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('q').value=''; document.getElementById('auditFilterForm').submit();" title="Borrar texto">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Fila 2: Filtros desplegables y fechas -->
        <div class="row g-2 align-items-end">
            <!-- Filtro Usuario -->
            <div class="col-12 col-sm-6 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1" for="user_id">Usuario</label>
                <select class="form-select form-select-sm" id="user_id" name="user_id">
                    <option value="">Todos los usuarios</option>
                    <?php foreach ($filterUsers as $u): ?>
                        <?php
                        $uId = (string) $u['id'];
                        $label = $u['name'] !== '' ? "{$u['name']} (@{$u['username']})" : "@{$u['username']}";
                        ?>
                        <option value="<?= e($uId) ?>" <?= $filters['user_id'] === $uId ? 'selected' : '' ?>>
                            <?= e($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filtro Acción -->
            <div class="col-12 col-sm-6 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1" for="action">Acción</label>
                <select class="form-select form-select-sm" id="action" name="action">
                    <option value="">Todas las acciones</option>
                    <?php foreach ($filterActions as $act): ?>
                        <option value="<?= e($act['action']) ?>" <?= $filters['action'] === $act['action'] ? 'selected' : '' ?>>
                            <?= e($act['label']) ?> (<?= e($act['action']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filtro Entidad -->
            <div class="col-12 col-sm-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1" for="entity">Entidad</label>
                <select class="form-select form-select-sm" id="entity" name="entity">
                    <option value="">Todas las entidades</option>
                    <?php foreach ($filterEntities as $ent): ?>
                        <option value="<?= e($ent['entity']) ?>" <?= $filters['entity'] === $ent['entity'] ? 'selected' : '' ?>>
                            <?= e($ent['label']) ?> (<?= e($ent['entity']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Fecha Desde -->
            <div class="col-6 col-sm-3 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1" for="date_from">Fecha desde</label>
                <input type="date"
                       class="form-control form-control-sm"
                       id="date_from"
                       name="date_from"
                       value="<?= e($filters['date_from']) ?>">
            </div>

            <!-- Fecha Hasta -->
            <div class="col-6 col-sm-3 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1" for="date_to">Fecha hasta</label>
                <input type="date"
                       class="form-control form-control-sm"
                       id="date_to"
                       name="date_to"
                       value="<?= e($filters['date_to']) ?>">
            </div>
        </div>

        <!-- Fila 3: Botones de acción -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 pt-2 border-top">
            <div class="small text-muted">
                <?php if ($hasActiveFilters): ?>
                    <span><i class="bi bi-funnel-fill text-primary me-1"></i> Filtros aplicados</span>
                <?php else: ?>
                    <span>Mostrando los registros más recientes</span>
                <?php endif; ?>
            </div>
            <div class="d-flex gap-2">
                <?php if ($hasActiveFilters): ?>
                    <a href="<?= e(url('/audit')) ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Limpiar filtros
                    </a>
                <?php endif; ?>
                <button type="submit" class="btn btn-sm btn-primary px-3">
                    <i class="bi bi-funnel me-1"></i> Filtrar
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Tabla de Registros -->
<div class="card-soft p-0 overflow-hidden mb-4 shadow-sm">
    <div class="p-3 bg-light border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="small text-muted fw-semibold">
            <i class="bi bi-list-ul me-1"></i>
            <?php
            $totalCount = (int) ($pagination['total'] ?? 0);
            $pageStart = $totalCount > 0 ? (($pagination['page'] - 1) * $pagination['per_page']) + 1 : 0;
            $pageEnd = min($totalCount, $pagination['page'] * $pagination['per_page']);
            ?>
            Mostrando <?= $pageStart ?> - <?= $pageEnd ?> de <?= number_format($totalCount) ?> evento<?= $totalCount === 1 ? '' : 's' ?>
        </div>
        <div class="small text-muted">
            Página <?= (int) ($pagination['page'] ?? 1) ?> de <?= max(1, (int) ($pagination['pages'] ?? 1)) ?>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
            <tr>
                <th scope="col" style="min-width: 140px;">Fecha y hora</th>
                <th scope="col" style="min-width: 180px;">Usuario</th>
                <th scope="col" style="min-width: 160px;">Acción</th>
                <th scope="col" style="min-width: 200px;">Entidad</th>
                <th scope="col" style="min-width: 110px;">IP</th>
                <th scope="col" class="text-end pe-3" style="min-width: 110px;">Detalle</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="bi bi-journal-x display-6 d-block mb-2 opacity-50"></i>
                        <p class="mb-1 fw-semibold">No se encontraron eventos registrados.</p>
                        <?php if ($hasActiveFilters): ?>
                            <p class="small mb-3">Pruebe ajustando los términos de búsqueda o limpie los filtros.</p>
                            <a href="<?= e(url('/audit')) ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Limpiar filtros
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($logs as $log): ?>
                    <?php
                    $rawDate = (string) ($log['created_at'] ?? '');
                    $displayDate = $formatDate($rawDate);
                    $displayName = (string) ($log['display_name'] ?? '—');
                    $username = (string) ($log['username'] ?? '');
                    $userRoles = (string) ($log['user_roles'] ?? '');
                    $actionLabel = (string) ($log['action_label'] ?? $log['action']);
                    $actionBadge = (string) ($log['action_badge'] ?? 'secondary');
                    $rawAction = (string) ($log['action'] ?? '');
                    $entityDisplay = (string) ($log['entity_display'] ?? '—');
                    $ip = (string) ($log['ip'] ?? '—');
                    $resultBadge = (string) ($log['result_badge'] ?? 'success');
                    $resultLabel = (string) ($log['result_label'] ?? 'Exitoso');

                    // Preparamos payload JSON seguro para el botón modal
                    $modalData = [
                        'id' => (int) ($log['id'] ?? 0),
                        'date' => $displayDate,
                        'user_name' => $displayName,
                        'username' => $username,
                        'user_roles' => $userRoles,
                        'action_label' => $actionLabel,
                        'action_code' => $rawAction,
                        'action_badge' => $actionBadge,
                        'entity_display' => $entityDisplay,
                        'entity_type' => (string) ($log['entity_label'] ?? $log['entity']),
                        'entity_id' => $log['entity_id'] ?? null,
                        'ip' => $ip,
                        'user_agent' => (string) ($log['user_agent'] ?? '—'),
                        'result_status' => (string) ($log['result_status'] ?? 'success'),
                        'result_label' => $resultLabel,
                        'result_badge' => $resultBadge,
                        'summary' => (string) ($log['summary'] ?? ''),
                        'old_values' => $log['parsed_old'] ?? null,
                        'new_values' => $log['parsed_new'] ?? null,
                    ];
                    $jsonData = htmlspecialchars(json_encode($modalData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                    ?>
                    <tr>
                        <!-- Fecha y hora -->
                        <td class="text-nowrap">
                            <span class="fw-semibold small d-block"><?= e(substr($displayDate, 0, 10)) ?></span>
                            <span class="text-muted small font-monospace"><?= e(substr($displayDate, 11)) ?></span>
                        </td>

                        <!-- Usuario -->
                        <td>
                            <div class="fw-semibold text-truncate" style="max-width: 220px;" title="<?= e($displayName) ?>">
                                <?= e($displayName) ?>
                            </div>
                            <div class="d-flex align-items-center gap-1 mt-1">
                                <?php if ($username !== '' && $username !== $displayName): ?>
                                    <span class="text-muted small">@<?= e($username) ?></span>
                                <?php endif; ?>
                                <?php if ($userRoles !== ''): ?>
                                    <span class="badge bg-light text-secondary border small py-0" style="font-size: 0.72rem;">
                                        <?= e($userRoles) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>

                        <!-- Acción -->
                        <td>
                            <span class="badge text-bg-<?= e($actionBadge) ?> px-2 py-1 mb-1 d-inline-block">
                                <?= e($actionLabel) ?>
                            </span>
                            <code class="d-block small text-muted font-monospace" style="font-size: 0.75rem;">
                                <?= e($rawAction) ?>
                            </code>
                        </td>

                        <!-- Entidad -->
                        <td>
                            <div class="text-truncate" style="max-width: 280px;" title="<?= e($entityDisplay) ?>">
                                <?= e($entityDisplay) ?>
                            </div>
                        </td>

                        <!-- IP -->
                        <td class="text-nowrap font-monospace small">
                            <i class="bi bi-laptop me-1 text-muted"></i><?= e($ip) ?>
                        </td>

                        <!-- Acción "Ver detalle" -->
                        <td class="text-end pe-3 text-nowrap">
                            <button type="button"
                                    class="btn btn-sm btn-outline-primary btn-audit-detail"
                                    data-bs-toggle="modal"
                                    data-bs-target="#auditDetailModal"
                                    data-audit="<?= $jsonData ?>">
                                <i class="bi bi-eye me-1"></i> Ver detalle
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Paginación -->
<?php if (($pagination['pages'] ?? 1) > 1): ?>
    <nav class="mt-3 mb-4" aria-label="Paginación de auditoría">
        <ul class="pagination justify-content-center">
            <?php
            $queryParams = array_filter([
                'q' => $filters['q'],
                'user_id' => $filters['user_id'],
                'action' => $filters['action'],
                'entity' => $filters['entity'],
                'date_from' => $filters['date_from'],
                'date_to' => $filters['date_to'],
            ], static fn ($v) => $v !== '' && $v !== null);

            $currentPage = (int) $pagination['page'];
            $totalPages = (int) $pagination['pages'];

            // Botón Anterior
            if ($currentPage > 1):
                $queryParams['page'] = $currentPage - 1;
                $prevUrl = url('/audit?' . http_build_query($queryParams));
                ?>
                <li class="page-item">
                    <a class="page-link" href="<?= e($prevUrl) ?>" aria-label="Anterior">
                        <span aria-hidden="true">&laquo; Anterior</span>
                    </a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <span class="page-link">&laquo; Anterior</span>
                </li>
            <?php endif; ?>

            <?php
            $range = 2;
            $startPage = max(1, $currentPage - $range);
            $endPage = min($totalPages, $currentPage + $range);

            if ($startPage > 1):
                $queryParams['page'] = 1;
                ?>
                <li class="page-item">
                    <a class="page-link" href="<?= e(url('/audit?' . http_build_query($queryParams))) ?>">1</a>
                </li>
                <?php if ($startPage > 2): ?>
                    <li class="page-item disabled"><span class="page-link">…</span></li>
                <?php endif; ?>
            <?php endif; ?>

            <?php for ($p = $startPage; $p <= $endPage; $p++):
                $queryParams['page'] = $p;
                $href = url('/audit?' . http_build_query($queryParams));
                ?>
                <li class="page-item <?= $p === $currentPage ? 'active' : '' ?>">
                    <a class="page-link" href="<?= e($href) ?>"><?= $p ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($endPage < $totalPages): ?>
                <?php if ($endPage < $totalPages - 1): ?>
                    <li class="page-item disabled"><span class="page-link">…</span></li>
                <?php endif; ?>
                <?php
                $queryParams['page'] = $totalPages;
                ?>
                <li class="page-item">
                    <a class="page-link" href="<?= e(url('/audit?' . http_build_query($queryParams))) ?>"><?= $totalPages ?></a>
                </li>
            <?php endif; ?>

            <!-- Botón Siguiente -->
            <?php if ($currentPage < $totalPages):
                $queryParams['page'] = $currentPage + 1;
                $nextUrl = url('/audit?' . http_build_query($queryParams));
                ?>
                <li class="page-item">
                    <a class="page-link" href="<?= e($nextUrl) ?>" aria-label="Siguiente">
                        <span aria-hidden="true">Siguiente &raquo;</span>
                    </a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <span class="page-link">Siguiente &raquo;</span>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
<?php endif; ?>

<!-- Modal de Detalle de Auditoría -->
<div class="modal fade" id="auditDetailModal" tabindex="-1" aria-labelledby="auditDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 bg-primary-subtle text-primary rounded-circle" style="width: 38px; height: 38px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-shield-check fs-5"></i>
                    </div>
                    <div>
                        <h2 class="modal-title fs-5 fw-bold mb-0 text-primary" id="auditDetailModalLabel">
                            Detalle del Evento de Auditoría
                        </h2>
                        <span class="text-muted small" id="modalAuditId">ID #</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Tarjeta Resumen Principal -->
                <div class="card-soft p-3 mb-3 bg-light border">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <span class="small text-muted d-block mb-1">Fecha y hora</span>
                            <div class="fw-semibold text-dark" id="modalDate">—</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <span class="small text-muted d-block mb-1">Resultado de la operación</span>
                            <div>
                                <span class="badge" id="modalResultBadge">—</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <span class="small text-muted d-block mb-1">Usuario</span>
                            <div class="fw-semibold text-dark" id="modalUser">—</div>
                            <span class="small text-muted" id="modalUserSub"></span>
                        </div>
                        <div class="col-12 col-md-6">
                            <span class="small text-muted d-block mb-1">Rol del usuario</span>
                            <div id="modalRoles">
                                <span class="badge bg-light text-secondary border">—</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <span class="small text-muted d-block mb-1">Acción</span>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge" id="modalActionBadge">—</span>
                                <code class="small text-muted font-monospace" id="modalActionCode"></code>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <span class="small text-muted d-block mb-1">Entidad afectada</span>
                            <div class="fw-semibold text-dark" id="modalEntity">—</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <span class="small text-muted d-block mb-1">Dirección IP</span>
                            <div class="font-monospace small text-dark" id="modalIp">—</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <span class="small text-muted d-block mb-1">Navegador / Cliente HTTP</span>
                            <div class="small text-muted text-truncate font-monospace" id="modalUserAgent" title="">—</div>
                        </div>
                    </div>
                </div>

                <!-- Detalle de la operación (Resumen contextual) -->
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-uppercase text-muted mb-2">
                        <i class="bi bi-info-circle me-1"></i> Detalle de la operación
                    </label>
                    <div class="p-3 bg-white border rounded-3" id="modalSummary">
                        —
                    </div>
                </div>

                <!-- Comparativa de datos (Valores anteriores y nuevos) -->
                <div id="modalValuesContainer" style="display: none;">
                    <label class="form-label fw-semibold small text-uppercase text-muted mb-2">
                        <i class="bi bi-sliders me-1"></i> Valores modificados
                    </label>
                    <div class="table-responsive border rounded-3 overflow-hidden mb-3">
                        <table class="table table-sm table-striped mb-0">
                            <thead class="table-light">
                            <tr>
                                <th scope="col" style="width: 30%;">Campo</th>
                                <th scope="col" style="width: 35%;">Valor anterior</th>
                                <th scope="col" style="width: 35%;">Valor nuevo</th>
                            </tr>
                            </thead>
                            <tbody id="modalValuesTableBody">
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Detalle Técnico (JSON / Metadatos) -->
                <div class="accordion accordion-flush border rounded-3 mt-3" id="modalRawAccordion">
                    <div class="accordion-item">
                        <h3 class="accordion-header" id="headingRaw">
                            <button class="accordion-button collapsed py-2 small text-muted" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRaw" aria-expanded="false" aria-controls="collapseRaw">
                                <i class="bi bi-code-square me-2"></i> Inspeccionar datos técnicos del evento (JSON)
                            </button>
                        </h3>
                        <div id="collapseRaw" class="accordion-collapse collapse" aria-labelledby="headingRaw" data-bs-parent="#modalRawAccordion">
                            <div class="accordion-body p-3 bg-light">
                                <pre class="mb-0 font-monospace small" style="max-height: 200px; overflow-y: auto;" id="modalRawJson"></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top py-2">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const detailButtons = document.querySelectorAll('.btn-audit-detail');

    detailButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const rawData = this.getAttribute('data-audit');
            if (!rawData) return;

            try {
                const data = JSON.parse(rawData);

                document.getElementById('modalAuditId').textContent = 'ID del evento #' + (data.id || '—');
                document.getElementById('modalDate').textContent = data.date || '—';

                // Resultado
                const resBadge = document.getElementById('modalResultBadge');
                resBadge.className = 'badge text-bg-' + (data.result_badge || 'success') + ' px-2 py-1';
                resBadge.innerHTML = (data.result_status === 'fail' ? '<i class="bi bi-x-circle me-1"></i>' : '<i class="bi bi-check-circle me-1"></i>') + (data.result_label || 'Exitoso');

                // Usuario
                document.getElementById('modalUser').textContent = data.user_name || '—';
                const userSub = document.getElementById('modalUserSub');
                if (data.username && data.username !== data.user_name) {
                    userSub.textContent = '@' + data.username;
                } else {
                    userSub.textContent = '';
                }

                // Roles
                const rolesContainer = document.getElementById('modalRoles');
                rolesContainer.innerHTML = '';
                if (data.user_roles) {
                    const roles = data.user_roles.split(',');
                    roles.forEach(function (r) {
                        const span = document.createElement('span');
                        span.className = 'badge bg-primary-subtle text-primary border border-primary-subtle me-1';
                        span.textContent = r.trim();
                        rolesContainer.appendChild(span);
                    });
                } else {
                    const span = document.createElement('span');
                    span.className = 'badge bg-light text-secondary border';
                    span.textContent = 'Sin rol asignado / Sistema';
                    rolesContainer.appendChild(span);
                }

                // Acción
                const actBadge = document.getElementById('modalActionBadge');
                actBadge.className = 'badge text-bg-' + (data.action_badge || 'secondary');
                actBadge.textContent = data.action_label || data.action_code || '—';
                document.getElementById('modalActionCode').textContent = data.action_code || '';

                // Entidad
                document.getElementById('modalEntity').textContent = data.entity_display || '—';

                // IP & User Agent
                document.getElementById('modalIp').textContent = data.ip || '—';
                const uaElem = document.getElementById('modalUserAgent');
                uaElem.textContent = data.user_agent || '—';
                uaElem.title = data.user_agent || '';

                // Summary
                document.getElementById('modalSummary').textContent = data.summary || 'Operación registrada en la auditoría.';

                // Comparativa de valores (old vs new)
                const valuesContainer = document.getElementById('modalValuesContainer');
                const tableBody = document.getElementById('modalValuesTableBody');
                tableBody.innerHTML = '';

                const oldVals = data.old_values || {};
                const newVals = data.new_values || {};
                const allKeys = Array.from(new Set([...Object.keys(oldVals), ...Object.keys(newVals)]));

                if (allKeys.length > 0) {
                    valuesContainer.style.display = 'block';
                    allKeys.forEach(function (key) {
                        const tr = document.createElement('tr');

                        const tdKey = document.createElement('td');
                        tdKey.className = 'fw-semibold small text-muted font-monospace';
                        tdKey.textContent = key;

                        const tdOld = document.createElement('td');
                        tdOld.className = 'small';
                        const oldVal = oldVals[key];
                        if (oldVal === undefined || oldVal === null) {
                            tdOld.innerHTML = '<span class="text-muted font-italic">—</span>';
                        } else if (typeof oldVal === 'object') {
                            tdOld.textContent = JSON.stringify(oldVal);
                        } else {
                            tdOld.textContent = String(oldVal);
                        }

                        const tdNew = document.createElement('td');
                        tdNew.className = 'small fw-semibold';
                        const newVal = newVals[key];
                        if (newVal === undefined || newVal === null) {
                            tdNew.innerHTML = '<span class="text-muted font-italic">—</span>';
                        } else if (typeof newVal === 'object') {
                            tdNew.textContent = JSON.stringify(newVal);
                        } else {
                            tdNew.textContent = String(newVal);
                        }

                        tr.appendChild(tdKey);
                        tr.appendChild(tdOld);
                        tr.appendChild(tdNew);
                        tableBody.appendChild(tr);
                    });
                } else {
                    valuesContainer.style.display = 'none';
                }

                // JSON técnico completo
                const cleanData = {
                    id: data.id,
                    created_at: data.date,
                    user_id: data.user_id,
                    username: data.username,
                    user_name: data.user_name,
                    user_roles: data.user_roles,
                    action: data.action_code,
                    entity: data.entity_type,
                    entity_id: data.entity_id,
                    ip: data.ip,
                    user_agent: data.user_agent,
                    old_values: data.old_values,
                    new_values: data.new_values
                };
                document.getElementById('modalRawJson').textContent = JSON.stringify(cleanData, null, 2);

            } catch (err) {
                console.error('Error al parsear datos de auditoría:', err);
            }
        });
    });
});
</script>
