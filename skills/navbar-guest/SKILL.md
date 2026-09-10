---
name: navbar-guest
description: Implementa la barra de navegación horizontal guest de SISTEMA_G_TECNICO_SAN_LORENZO con Inicio de sesión y Registro, responsive (collapse &lt; lg). Usar al diseñar o modificar navbar pública, layout auth/guest, enlaces de login/registro o navegación para visitantes.
---

# Navbar guest horizontal — SISTEMA_G_TECNICO_SAN_LORENZO

## Nombre

`navbar-guest`

## Propósito

Implementar la navbar superior **horizontal** para visitantes no autenticados: marca institucional + **Inicio de sesión** + **Registro**, responsive con colapso Bootstrap en móvil/tablet, sin mezclarla con la navbar autenticada ni el sidebar (SPEC 26).

## Cuándo usarla

- Al añadir o modificar navegación en login/registro o layout guest.
- Al pedir “barra de navegación con inicio de sesión y registro”.
- Al ajustar responsive del acceso público (toggler guest).
- Antes de tocar `layouts/auth.php` o partials de navegación pública.
- **No usar** para menú de usuario logueado, campana de notificaciones o sidebar.

## SPEC relacionada

- `spec/28-navbar-guest.md` (**obligatoria**)
- `spec/06-ui-ux.md`
- `spec/07-autenticacion.md`
- `spec/26-navegacion-movil.md` (contraste: sidebar vs collapse guest)

## Pasos de implementación

1. Leer `spec/28-navbar-guest.md` completa y esta skill.
2. Crear partial `app/views/partials/navbar-guest.php`.
3. Incluir el partial al inicio del `<body>` en `app/views/layouts/auth.php` (antes de la card de formulario).
4. Estructura Bootstrap 5:
   - `nav.navbar.navbar-expand-lg.guest-navbar`
   - Marca a la izquierda (`navbar-brand` + escudo opcional).
   - `button.navbar-toggler` con `d-lg-none`, `data-bs-toggle="collapse"`, `data-bs-target="#guestNavCollapse"`.
   - `#guestNavCollapse.collapse.navbar-collapse` con enlaces alineados a la derecha (`ms-auto`).
5. Enlaces:
   - Inicio de sesión → `url('/login')`
   - Registro → `url('/register')`
6. Marcar `.active` / `aria-current="page"` según path actual.
7. Estilos en `app/assets/css/app.css` con tokens `--pi-*` / brand (celeste/azul/blanco).
8. Si navbar es `fixed-top`, añadir padding al contenedor auth para que no tape el formulario.
9. Verificar que `navbar.php` autenticada **no** se altere salvo necesidad de evitar colisión de IDs (`#sidebarToggle` ≠ `#guestNavToggle`).
10. Probar breakpoints 390px y 1366px; marcar SPEC 28 como COMPLETED solo si pasan los criterios.

## Convenciones de código

- Textos: **«Inicio de sesión»** y **«Registro»** (español).
- IDs: `#guestNavbar`, `#guestNavToggle`, `#guestNavCollapse`.
- Clase raíz: `.guest-navbar` (no reutilizar `.app-navbar` del layout autenticado sin adaptar).
- Escape: `e()` en marca y URLs.
- Rutas solo con helper `url()`.
- CTA: Registro como `btn btn-primary` (o equivalente temático); Login como `btn btn-outline-light` / link según contraste del fondo.
- Un solo H1 de página lo aportan las vistas auth; la marca de navbar usa `navbar-brand`, no otro H1.
- No AJAX obligatorio; JS = bundle Bootstrap ya presente en layout auth.

### Wireframe de referencia

```html
<nav id="guestNavbar" class="navbar navbar-expand-lg guest-navbar">
  <div class="container">
    <a class="navbar-brand" href="<?= e(url('/login')) ?>">…marca…</a>
    <button id="guestNavToggle" class="navbar-toggler" type="button"
            data-bs-toggle="collapse" data-bs-target="#guestNavCollapse"
            aria-controls="guestNavCollapse" aria-expanded="false"
            aria-label="Abrir menú de acceso">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="guestNavCollapse">
      <div class="navbar-nav ms-auto align-items-lg-center gap-2">
        <a class="nav-link …" href="<?= e(url('/login')) ?>">Inicio de sesión</a>
        <a class="btn …" href="<?= e(url('/register')) ?>">Registro</a>
      </div>
    </div>
  </div>
</nav>
```

## Checklist de seguridad

- [ ] Sin datos de sesión ni permisos en el partial guest.
- [ ] URLs internas escapadas; sin destinos abiertos.
- [ ] No confundir toggler guest con `#sidebarToggle` (SPEC 26).
- [ ] Formularios login/registro siguen CSRF (SPEC 07); la navbar no añade POST inseguros.
- [ ] Middleware `guest` sigue protegiendo rutas; navbar no sustituye authz.

## Checklist de pruebas

- [ ] NG-01…NG-08 de `spec/28-navbar-guest.md`.
- [ ] Login y registro siguen enviando formularios correctamente bajo la navbar.
- [ ] Desktop: ambos enlaces visibles sin toggler.
- [ ] Móvil: toggler abre/cierra collapse.
- [ ] Activo correcto en `/login` vs `/register`.
- [ ] Autenticado en `/login` → redirect; no se ve navbar guest.
- [ ] Sin scroll horizontal en 320px.
- [ ] Layout autenticado (sidebar + hamburguesa) sin regresiones.

## Errores comunes a evitar

- Reutilizar el partial `navbar.php` autenticado en layout auth.
- Poner hamburguesa de sidebar en páginas guest.
- Olvidar estado activo o usar solo color sin `aria-current`.
- Navbar que tapa el formulario (`fixed-top` sin offset).
- Mezclar Bootstrap 4 (`data-toggle`) con Bootstrap 5 (`data-bs-toggle`).
- Implementar código sin haber leído SPEC 28 (viola POLKDEV).

## Criterio de done

Navbar guest está **done** cuando el partial existe, el layout auth lo incluye, los enlaces Inicio de sesión / Registro funcionan, el comportamiento responsive cumple SPEC 28, la identidad visual es institucional, no hay regresión en SPEC 26, y los criterios de aceptación de la SPEC están marcados COMPLETED.
