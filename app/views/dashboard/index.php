<?php
/** @var array $user */
/** @var array $metrics */
$roles = $user['roles'] ?? [];
$cards = $metrics['cards'] ?? [];
$recent = $metrics['recent_announcements'] ?? [];
$roleView = (string) ($metrics['role_view'] ?? '');
$userName = (string) ($user['name'] ?? 'usuario');
$portalName = (string) config('app.name', 'Portal UE San Lorenzo');
$vicerrectorActivities = $metrics['vicerrector_activities'] ?? [
    'items' => [],
    'total_files' => 0,
    'total_announcements' => 0,
];

$cardByLabel = [];
foreach ($cards as $card) {
    $label = (string) ($card['label'] ?? '');
    if ($label !== '') {
        $cardByLabel[$label] = $card;
    }
}

$pick = static function (array $map, array $labels, array $fallbackCards, int $index): ?array {
    foreach ($labels as $label) {
        if (isset($map[$label])) {
            return $map[$label];
        }
    }
    return $fallbackCards[$index] ?? null;
};

$avisoCard = $pick($cardByLabel, ['Avisos', 'Avisos vigentes', 'Mis avisos', 'Publicados'], $cards, 0);
$notifCard = $pick($cardByLabel, ['No leídas'], $cards, 1);
$auditCard = $pick($cardByLabel, ['Auditoría (7d)', 'Archivados', 'Borradores'], $cards, 2);

$used = array_filter([$avisoCard, $notifCard, $auditCard]);
$extraCards = [];
foreach ($cards as $card) {
    $label = (string) ($card['label'] ?? '');
    // Se excluye la tarjeta de usuarios en favor del cuadro de actividades del vicerrectorado
    if ($label === 'Usuarios') {
        continue;
    }
    $already = false;
    foreach ($used as $u) {
        if ($u === $card) {
            $already = true;
            break;
        }
    }
    if (!$already) {
        $extraCards[] = $card;
    }
}

$avisoCount = (int) ($avisoCard['value'] ?? count($recent));
$unreadCount = (int) ($notifCard['value'] ?? 0);
$auditValue = $auditCard['value'] ?? '0';
?>
<section class="dash-hero">
    <div class="dash-hero-brand">
        <h1>U.E.F. San Lorenzo</h1>
        <img
            class="brand-shield"
            src="<?= e(asset('img/escudo-san-lorenzo.png')) ?>"
            alt="Escudo institucional Unidad Educativa Fiscomisional San Lorenzo"
            width="78"
            height="78"
        >
        <div class="dash-welcome">
            <p class="welcome-name">Bienvenido(a) <?= e($userName) ?></p>
            <p class="welcome-portal">Bienvenido(a), <?= e($portalName) ?></p>
            <p class="small text-muted mb-0">
                Vista: <?= e($roleView !== '' ? $roleView : 'General') ?>
                · Roles: <?= e($roles !== [] ? implode(', ', array_map('strval', $roles)) : 'Sin roles') ?>
            </p>
        </div>
    </div>
    <div class="dash-hero-actions">
        <?php if (auth_can('announcements.view')): ?>
            <a class="btn btn-outline-primary" href="<?= e(url('/announcements')) ?>">Ir a avisos</a>
        <?php endif; ?>
        <?php if (auth_can('notifications.view')): ?>
            <a class="btn btn-outline-primary" href="<?= e(url('/notifications')) ?>">Ir a notificaciones</a>
        <?php endif; ?>
        <?php if (auth_can('announcements.create')): ?>
            <a class="btn btn-primary" href="<?= e(url('/announcements/create')) ?>">Crear aviso</a>
        <?php endif; ?>
    </div>
</section>

<div class="dash-grid">
    <!-- Cuadro: Actividades del Vicerrectorado (Archivos, circulares y gestión académica) -->
    <article class="widget widget-glass widget-vicerrector" id="widget-vicerrector">
        <div class="widget-head d-flex justify-content-between align-items-center mb-2">
            <div class="d-flex align-items-center gap-2">
                <span class="widget-badge-icon" aria-hidden="true">
                    <i class="bi bi-briefcase-fill text-primary"></i>
                </span>
                <div>
                    <h2 class="h5 mb-0 fw-bold">Actividades del Vicerrectorado</h2>
                    <span class="small text-muted">Archivos, circulares y documentos</span>
                </div>
            </div>
            <?php if (auth_can('announcements.create') || auth_can('files.upload')): ?>
                <a href="<?= e(url('/announcements/create')) ?>" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1 shadow-sm" title="Subir nuevo archivo o registrar actividad">
                    <i class="bi bi-cloud-arrow-up-fill"></i>
                    <span class="d-none d-sm-inline">Subir archivo / aviso</span>
                </a>
            <?php endif; ?>
        </div>

        <!-- Mini resumen y filtros -->
        <div class="vicerrector-toolbar my-2 p-2 rounded-3 bg-light d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex gap-3 small align-items-center">
                <span class="text-secondary">
                    <i class="bi bi-file-earmark-arrow-up-fill text-primary me-1"></i>
                    <strong><?= (int) ($vicerrectorActivities['total_files'] ?? 0) ?></strong> archivos
                </span>
                <span class="text-secondary">
                    <i class="bi bi-megaphone-fill text-info me-1"></i>
                    <strong><?= (int) ($vicerrectorActivities['total_announcements'] ?? 0) ?></strong> comunicados
                </span>
            </div>
            <div class="btn-group btn-group-sm" role="group" id="vicerrector-filters" aria-label="Filtro de actividades">
                <button type="button" class="btn btn-primary btn-sm active" data-filter="all">Todos</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-filter="file">Archivos</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-filter="announcement">Avisos</button>
            </div>
        </div>

        <!-- Feed de actividades y archivos -->
        <div class="vicerrector-feed" id="vicerrector-feed">
            <?php if (!empty($vicerrectorActivities['items'])): ?>
                <?php foreach ($vicerrectorActivities['items'] as $item): ?>
                    <?php if ($item['type'] === 'file'): ?>
                        <?php
                            $ext = strtolower((string) ($item['extension'] ?? ''));
                            $iconInfo = match ($ext) {
                                'doc', 'docx' => ['icon' => 'bi-file-earmark-word-fill', 'class' => 'file-word', 'badge' => 'bg-primary-subtle text-primary border border-primary-subtle'],
                                'xls', 'xlsx' => ['icon' => 'bi-file-earmark-excel-fill', 'class' => 'file-excel', 'badge' => 'bg-success-subtle text-success border border-success-subtle'],
                                'pdf' => ['icon' => 'bi-file-earmark-pdf-fill', 'class' => 'file-pdf', 'badge' => 'bg-danger-subtle text-danger border border-danger-subtle'],
                                'ppt', 'pptx' => ['icon' => 'bi-file-earmark-slides-fill', 'class' => 'file-ppt', 'badge' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'],
                                'zip', 'rar' => ['icon' => 'bi-file-earmark-zip-fill', 'class' => 'file-zip', 'badge' => 'bg-secondary-subtle text-secondary border border-secondary-subtle'],
                                'jpg', 'jpeg', 'png', 'webp' => ['icon' => 'bi-file-earmark-image-fill', 'class' => 'file-img', 'badge' => 'bg-info-subtle text-info border border-info-subtle'],
                                default => ['icon' => 'bi-file-earmark-text-fill', 'class' => 'file-other', 'badge' => 'bg-secondary-subtle text-secondary border border-secondary-subtle'],
                            };
                            $sizeHuman = '0 B';
                            $bytes = (int) ($item['size_bytes'] ?? 0);
                            if ($bytes > 0) {
                                $units = ['B', 'KB', 'MB', 'GB'];
                                $pow = min((int) floor(log($bytes, 1024)), count($units) - 1);
                                $sizeHuman = round($bytes / (1024 ** $pow), 1) . ' ' . $units[$pow];
                            }
                            $rawDate = (string) ($item['date'] ?? '');
                            $displayDate = $rawDate;
                            if ($rawDate !== '') {
                                $time = strtotime($rawDate);
                                if ($time !== false) {
                                    $months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                                    $displayDate = date('j', $time) . ' ' . ($months[(int) date('n', $time) - 1] ?? '') . ' ' . date('Y', $time);
                                }
                            }
                        ?>
                        <div class="vicerrector-item vicerrector-item-file" data-type="file">
                            <div class="vicerrector-item-icon <?= e($iconInfo['class']) ?>" aria-hidden="true">
                                <i class="bi <?= e($iconInfo['icon']) ?>"></i>
                            </div>
                            <div class="vicerrector-item-body">
                                <div class="vicerrector-item-title-row">
                                    <?php if (auth_can('files.download')): ?>
                                        <a href="<?= e($item['download_url']) ?>" class="vicerrector-item-title text-decoration-none fw-semibold text-truncate" title="<?= e($item['title']) ?>">
                                            <?= e($item['title']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="vicerrector-item-title fw-semibold text-truncate" title="<?= e($item['title']) ?>">
                                            <?= e($item['title']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="badge <?= e($iconInfo['badge']) ?> font-monospace" style="font-size: 0.7rem;"><?= strtoupper(e($ext ?: 'FILE')) ?></span>
                                </div>
                                <div class="vicerrector-item-meta">
                                    <?php if (!empty($item['detail']) && !empty($item['view_url'])): ?>
                                        <a href="<?= e($item['view_url']) ?>" class="text-decoration-none text-muted text-truncate meta-link" title="<?= e($item['detail']) ?>">
                                            <i class="bi bi-paperclip"></i> <?= e($item['detail']) ?>
                                        </a>
                                        <span class="bullet">·</span>
                                    <?php endif; ?>
                                    <span class="text-muted"><?= e($sizeHuman) ?></span>
                                    <span class="bullet">·</span>
                                    <span class="text-muted" title="<?= e($rawDate) ?>"><?= e($displayDate) ?></span>
                                </div>
                            </div>
                            <div class="vicerrector-item-action">
                                <?php if (auth_can('files.download')): ?>
                                    <a href="<?= e($item['download_url']) ?>" class="btn btn-sm btn-outline-primary btn-icon" title="Descargar <?= e($item['title']) ?>">
                                        <i class="bi bi-download"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php
                            $rawDate = (string) ($item['date'] ?? '');
                            $displayDate = $rawDate;
                            if ($rawDate !== '') {
                                $time = strtotime($rawDate);
                                if ($time !== false) {
                                    $months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                                    $displayDate = date('j', $time) . ' ' . ($months[(int) date('n', $time) - 1] ?? '') . ' ' . date('Y', $time);
                                }
                            }
                        ?>
                        <div class="vicerrector-item vicerrector-item-announcement" data-type="announcement">
                            <div class="vicerrector-item-icon bg-info-subtle text-primary" aria-hidden="true">
                                <i class="bi bi-megaphone-fill"></i>
                            </div>
                            <div class="vicerrector-item-body">
                                <div class="vicerrector-item-title-row">
                                    <a href="<?= e($item['view_url']) ?>" class="vicerrector-item-title text-decoration-none fw-semibold text-truncate" title="<?= e($item['title']) ?>">
                                        <?= e($item['title']) ?>
                                    </a>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.7rem;">Aviso</span>
                                </div>
                                <div class="vicerrector-item-meta">
                                    <span class="text-muted text-truncate" style="max-width: 180px;"><?= e($item['detail']) ?></span>
                                    <span class="bullet">·</span>
                                    <span class="text-muted" title="<?= e($rawDate) ?>"><?= e($displayDate) ?></span>
                                    <?php if (!empty($item['files_count'])): ?>
                                        <span class="bullet">·</span>
                                        <span class="badge bg-light text-secondary border"><i class="bi bi-paperclip"></i> <?= (int) $item['files_count'] ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="vicerrector-item-action">
                                <a href="<?= e($item['view_url']) ?>" class="btn btn-sm btn-outline-secondary btn-icon" title="Ver comunicado">
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="vicerrector-empty text-center py-4 text-muted">
                    <i class="bi bi-folder2-open display-6 d-block mb-2 text-primary opacity-50"></i>
                    <p class="small mb-2">No hay actividades recientes registradas por el Vicerrectorado.</p>
                    <?php if (auth_can('announcements.create') || auth_can('files.upload')): ?>
                        <a href="<?= e(url('/announcements/create')) ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-plus-lg me-1"></i>Subir primer archivo o aviso
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="vicerrector-filtered-empty text-center py-4 text-muted" style="display: none;">
                <i class="bi bi-funnel display-6 d-block mb-2 text-secondary opacity-50"></i>
                <p class="small mb-0">No se encontraron elementos para el filtro seleccionado.</p>
            </div>
        </div>
    </article>

    <!-- Avisos institucionales -->
    <article class="widget widget-glass widget-avisos">
        <div class="widget-head">
            <h2><?= e((string) ($avisoCount)) ?> <?= e((string) ($avisoCard['label'] ?? 'Avisos')) ?></h2>
            <?php if (auth_can('announcements.view')): ?>
                <a href="<?= e(url('/announcements')) ?>">Ver todos</a>
            <?php endif; ?>
        </div>
        <?php if ($recent !== []): ?>
            <?php foreach ($recent as $row): ?>
                <a class="aviso-card" href="<?= e(url('/announcements/' . $row['id'])) ?>">
                    <span class="aviso-thumb"><i class="bi bi-megaphone"></i></span>
                    <span>
                        <strong><?= e((string) $row['title']) ?></strong>
                        <span><?= e((string) (!empty($row['description']) ? $row['description'] : ($row['publish_at'] ?? ''))) ?></span>
                    </span>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted small mb-0">
                <?= e((string) ($avisoCard['hint'] ?? 'Consulte el módulo de avisos para el listado completo.')) ?>
            </p>
            <?php if (auth_can('announcements.view')): ?>
                <a class="btn btn-outline-primary btn-sm mt-3" href="<?= e(url('/announcements')) ?>">Abrir avisos</a>
            <?php endif; ?>
        <?php endif; ?>
    </article>

    <!-- Notificaciones -->
    <?php if ($notifCard !== null): ?>
        <a class="widget widget-glass widget-notif text-decoration-none" href="<?= e(auth_can('notifications.view') ? url('/notifications') : url('/dashboard')) ?>">
            <div class="bell-wrap">
                <i class="bi <?= e((string) ($notifCard['icon'] ?? 'bi-bell')) ?>"></i>
                <span class="bell-badge"><?= e((string) $unreadCount) ?></span>
            </div>
            <div class="fw-semibold mt-2"><?= e((string) ($notifCard['label'] ?? 'Notificaciones')) ?></div>
            <div class="small text-muted"><?= e((string) ($notifCard['hint'] ?? '')) ?></div>
        </a>
    <?php endif; ?>

    <!-- Auditoría o métricas -->
    <?php if ($auditCard !== null): ?>
        <article class="widget widget-glass widget-audit">
            <div class="widget-head">
                <h2><?= e((string) $auditValue) ?> <?= e((string) ($auditCard['label'] ?? 'Eventos')) ?></h2>
            </div>
            <div class="radar-wrap">
                <svg class="radar-svg" viewBox="0 0 120 120" aria-hidden="true">
                    <polygon points="60,12 104,42 88,96 32,96 16,42" fill="none" stroke="rgba(26,86,176,0.25)" stroke-width="1"></polygon>
                    <polygon points="60,28 90,48 80,84 40,84 30,48" fill="none" stroke="rgba(26,86,176,0.35)" stroke-width="1"></polygon>
                    <polygon points="60,22 96,46 82,90 38,90 24,46" fill="rgba(26,86,176,0.12)" stroke="#1a56b0" stroke-width="1.6"></polygon>
                    <circle cx="60" cy="60" r="2.5" fill="#1a56b0"></circle>
                </svg>
            </div>
            <div class="audit-log">
                <div><?= e((string) $auditValue) ?> <?= e((string) ($auditCard['label'] ?? 'eventos')) ?> — <?= e((string) ($auditCard['hint'] ?? '')) ?></div>
                <?php if (auth_can('audit.view')): ?>
                    <div><a href="<?= e(url('/audit')) ?>">Abrir registro de auditoría</a></div>
                <?php endif; ?>
            </div>
        </article>
    <?php endif; ?>

    <!-- Tarjetas adicionales si existen -->
    <?php if ($extraCards !== []): ?>
        <div class="widget-extra">
            <?php foreach ($extraCards as $card): ?>
                <article class="widget widget-glass p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="small text-muted"><?= e((string) ($card['label'] ?? '')) ?></div>
                            <div class="fs-3 fw-semibold"><?= e((string) ($card['value'] ?? '0')) ?></div>
                            <div class="small text-muted"><?= e((string) ($card['hint'] ?? '')) ?></div>
                        </div>
                        <i class="bi <?= e((string) ($card['icon'] ?? 'bi-circle')) ?> fs-4 text-brand"></i>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
