<?php
$current = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$user = auth_user();

$items = [
    ['label' => 'Dashboard', 'path' => '/dashboard', 'icon' => 'bi-speedometer2', 'permission' => null],
    ['label' => 'Avisos', 'path' => '/announcements', 'icon' => 'bi-megaphone', 'permission' => 'announcements.view'],
    ['label' => 'Notificaciones', 'path' => '/notifications', 'icon' => 'bi-bell', 'permission' => 'notifications.view'],
    ['label' => 'Usuarios', 'path' => '/users', 'icon' => 'bi-people', 'permission' => 'users.view'],
    ['label' => 'Roles', 'path' => '/roles', 'icon' => 'bi-shield-lock', 'permission' => 'roles.view'],
    ['label' => 'Auditoría', 'path' => '/audit', 'icon' => 'bi-journal-text', 'permission' => 'audit.view'],
    ['label' => 'Mi perfil', 'path' => '/profile', 'icon' => 'bi-person-circle', 'permission' => null],
];
?>
<aside class="app-sidebar" id="app-sidebar" aria-label="Menú principal">
    <div class="brand d-flex align-items-start justify-content-between gap-2">
        <div>
            <h1>Portal UE San Lorenzo</h1>
            <small>Fiscomisional</small>
        </div>
        <button
            type="button"
            class="btn btn-sm btn-light d-lg-none sidebar-close"
            id="sidebarClose"
            aria-label="Cerrar menú"
        >
            <i class="bi bi-x-lg" aria-hidden="true"></i>
        </button>
    </div>
    <nav class="nav flex-column py-3">
        <?php foreach ($items as $item): ?>
            <?php if ($item['permission'] !== null && !auth_can($item['permission'])) {
                continue;
            } ?>
            <?php
            $href = url($item['path']);
            $active = str_contains((string) $current, $item['path']) ? 'active' : '';
            ?>
            <a class="nav-link <?= e($active) ?>" href="<?= e($href) ?>">
                <i class="bi <?= e($item['icon']) ?> me-2" aria-hidden="true"></i><?= e($item['label']) ?>
            </a>
        <?php endforeach; ?>
    </nav>
    <?php if ($user): ?>
        <div class="px-3 pb-3 mt-auto small text-white-50">
            <?= e((string) ($user['name'] ?? 'Usuario')) ?>
        </div>
    <?php endif; ?>
</aside>
<div class="sidebar-backdrop d-lg-none" id="sidebarBackdrop" hidden></div>
