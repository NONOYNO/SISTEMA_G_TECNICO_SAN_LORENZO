<?php
/** @var array $user */
/** @var array $metrics */
$roles = $user['roles'] ?? [];
$cards = $metrics['cards'] ?? [];
$recent = $metrics['recent_announcements'] ?? [];
$roleView = (string) ($metrics['role_view'] ?? '');
$userName = (string) ($user['name'] ?? 'usuario');
$portalName = (string) config('app.name', 'Portal UE San Lorenzo');

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

$userCard = $pick($cardByLabel, ['Usuarios'], $cards, 0);
$avisoCard = $pick($cardByLabel, ['Avisos', 'Avisos vigentes', 'Mis avisos', 'Publicados'], $cards, 1);
$notifCard = $pick($cardByLabel, ['No leídas'], $cards, 2);
$auditCard = $pick($cardByLabel, ['Auditoría (7d)', 'Archivados', 'Borradores'], $cards, 3);

$used = array_filter([$userCard, $avisoCard, $notifCard, $auditCard]);
$extraCards = [];
foreach ($cards as $card) {
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
$userValue = $userCard['value'] ?? '0';
$auditValue = $auditCard['value'] ?? '0';
$barHeights = [38, 55, 42, 70, 48, 82, 60];
if (is_numeric($userValue)) {
    $seed = max(1, (int) $userValue);
    $barHeights = [
        30 + ($seed * 7) % 40,
        40 + ($seed * 11) % 45,
        28 + ($seed * 5) % 50,
        50 + ($seed * 13) % 35,
        36 + ($seed * 3) % 48,
        44 + ($seed * 17) % 40,
        32 + ($seed * 9) % 52,
    ];
}
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
    <?php if ($userCard !== null): ?>
        <article class="widget widget-users">
            <i class="bi <?= e((string) ($userCard['icon'] ?? 'bi-people')) ?> widget-icon" aria-hidden="true"></i>
            <div class="widget-value"><?= e((string) $userValue) ?></div>
            <div class="widget-kicker"><?= e((string) ($userCard['label'] ?? 'Usuarios')) ?> — <?= e((string) ($userCard['hint'] ?? 'Activos ahora')) ?></div>
            <svg class="chart-line" viewBox="0 0 240 54" aria-hidden="true">
                <path d="M4 40 C 28 38, 40 18, 62 22 S 100 46, 124 28 S 170 8, 196 20 S 220 36, 236 16"></path>
            </svg>
            <div class="chart-bars" aria-hidden="true">
                <?php foreach ($barHeights as $h): ?>
                    <span style="height: <?= (int) $h ?>%"></span>
                <?php endforeach; ?>
            </div>
        </article>
    <?php endif; ?>

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

    <?php if ($extraCards !== []): ?>
        <div class="widget-extra d-flex flex-column gap-3">
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
