<?php
$currentPath = (string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$isLogin = str_ends_with(rtrim($currentPath, '/'), '/login') || str_contains($currentPath, '/login');
$isRegister = str_ends_with(rtrim($currentPath, '/'), '/register') || str_contains($currentPath, '/register');
$appName = (string) config('app.name', 'Portal UE San Lorenzo');
?>
<nav id="guestNavbar" class="navbar navbar-expand-lg guest-navbar sticky-top" aria-label="Navegación de acceso">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2 min-w-0" href="<?= e(url('/login')) ?>">
            <img
                src="<?= e(asset('img/escudo-san-lorenzo.png')) ?>"
                alt=""
                width="36"
                height="36"
                class="guest-navbar-shield"
            >
            <span class="guest-navbar-brand-text text-truncate">
                <span class="d-block fw-semibold lh-sm">UEF San Lorenzo</span>
                <span class="d-none d-sm-block small opacity-75 fw-normal"><?= e($appName) ?></span>
            </span>
        </a>

        <button
            id="guestNavToggle"
            class="navbar-toggler guest-navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#guestNavCollapse"
            aria-controls="guestNavCollapse"
            aria-expanded="false"
            aria-label="Abrir menú de acceso"
        >
            <i class="bi bi-list fs-3" aria-hidden="true"></i>
        </button>

        <div class="collapse navbar-collapse" id="guestNavCollapse">
            <div class="navbar-nav ms-auto align-items-lg-center gap-2 py-3 py-lg-0">
                <a
                    class="btn btn-sm guest-nav-login<?= $isLogin ? ' active' : '' ?>"
                    href="<?= e(url('/login')) ?>"
                    <?= $isLogin ? 'aria-current="page"' : '' ?>
                >
                    <i class="bi bi-box-arrow-in-right me-1" aria-hidden="true"></i>
                    Inicio de sesión
                </a>
                <a
                    class="btn btn-sm guest-nav-register<?= $isRegister ? ' active' : '' ?>"
                    href="<?= e(url('/register')) ?>"
                    <?= $isRegister ? 'aria-current="page"' : '' ?>
                >
                    <i class="bi bi-person-plus me-1" aria-hidden="true"></i>
                    Registro
                </a>
            </div>
        </div>
    </div>
</nav>
