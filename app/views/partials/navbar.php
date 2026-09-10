<?php
$user = auth_user();
$displayName = (string) ($user['name'] ?? 'Cuenta');
$parts = preg_split('/\s+/', trim($displayName)) ?: [];
$initials = strtoupper(
    substr((string) ($parts[0] ?? 'U'), 0, 1) .
    substr((string) ($parts[1] ?? ''), 0, 1)
);
if ($initials === '') {
    $initials = 'U';
}
?>
<nav class="navbar navbar-expand-lg app-navbar px-3">
    <div class="container-fluid gap-2">
        <div class="d-flex align-items-center gap-2 min-w-0">
            <button
                type="button"
                class="btn btn-outline-primary sidebar-toggle d-lg-none"
                id="sidebarToggle"
                aria-controls="app-sidebar"
                aria-expanded="false"
                aria-label="Abrir menú de navegación"
            >
                <i class="bi bi-list fs-4" aria-hidden="true" data-icon="open"></i>
                <i class="bi bi-x-lg fs-4 d-none" aria-hidden="true" data-icon="close"></i>
            </button>
            <span class="navbar-brand mb-0 h1 fs-6 fw-semibold text-truncate">
                <?= e((string) config('app.name', 'Portal UE San Lorenzo')) ?>
            </span>
        </div>
        <div class="d-flex align-items-center gap-2 gap-md-3 ms-auto">
            <?php if (auth_can('notifications.view')): ?>
                <a href="<?= e(url('/notifications')) ?>" class="text-decoration-none text-light" title="Notificaciones">
                    <i class="bi bi-bell fs-5"></i>
                </a>
            <?php endif; ?>
            <div class="dropdown">
                <button
                    class="btn btn-link text-decoration-none d-flex align-items-center gap-2 p-0"
                    type="button"
                    id="userMenuButton"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    aria-haspopup="true"
                >
                    <span class="user-avatar" aria-hidden="true"><?= e($initials) ?></span>
                    <span class="d-none d-md-inline text-light small"><?= e($displayName) ?></span>
                    <i class="bi bi-chevron-down text-light small"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenuButton">
                    <li>
                        <a class="dropdown-item py-2" href="<?= e(url('/profile')) ?>">Mi perfil</a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <button type="submit" form="logoutForm" class="dropdown-item py-2 text-danger">Cerrar sesión</button>
                    </li>
                </ul>
            </div>
            <form id="logoutForm" method="post" action="<?= e(url('/logout')) ?>" class="d-none">
                <?= csrf_field() ?>
            </form>
            <button type="button" class="btn-soporte" data-bs-toggle="modal" data-bs-target="#soporteModal">
                Soporte
            </button>
        </div>
    </div>
</nav>
