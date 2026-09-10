---
name: bootstrap-ui
description: Aplica UI Bootstrap 5 institucional de SISTEMA_G_TECNICO_SAN_LORENZO con paleta celeste/azul/blanco, cards, navbar, sidebar y responsive. Usar al diseñar vistas, dashboards, formularios, alertas o componentes visuales.
---

# Bootstrap UI — SISTEMA_G_TECNICO_SAN_LORENZO

## Nombre

`bootstrap-ui`

## Propósito

Definir la interfaz institucional del portal de la Unidad Educativa Fiscomisional San Lorenzo con Bootstrap 5: paleta celeste/azul/blanco, componentes consistentes (navbar, sidebar, cards, modales, tablas, formularios) y experiencia responsive en desktop, tablet y móvil.

## Cuándo usarla

- Al crear o modificar cualquier vista Blade/PHP.
- Al implementar login, dashboard, listados, cards de notificaciones/avisos.
- Al ajustar temas CSS, tipografía o componentes.
- Al revisar que la UI transmita educación, formalidad, confianza y modernidad.
- Antes de aceptar una pantalla como “lista”.

## SPEC relacionada

- `spec/06-ui-ux.md`
- `spec/26-navegacion-movil.md`
- `spec/28-navbar-guest.md` (navbar guest: Inicio de sesión / Registro)
- `spec/13-dashboard.md`
- `spec/10-avisos.md`
- `spec/11-notificaciones.md`
- `spec/00-master-spec.md`

## Pasos de implementación

1. Leer `spec/06-ui-ux.md` y esta skill.
2. Definir variables CSS institucionales en `app/assets/css/` (ej. `theme.css`).
3. Fijar paleta:

| Token | Uso | Guía |
|-------|-----|------|
| `--brand-primary` | Azul institucional | acciones principales, navbar |
| `--brand-secondary` | Celeste | hover, fondos suaves, badges |
| `--brand-surface` | Blanco | cards, paneles |
| `--brand-text` | Azul oscuro / gris seguro | texto legible |
| `--brand-muted` | Celeste/gris claro | fondos de página |

4. Crear layout base: `layouts/main.php` (navbar + sidebar + content + footer).
5. Reutilizar partials: alerts, breadcrumbs, pagination, empty-states.
6. Login/registro: composición limpia, marca visible, sin saturar.
7. Dashboards: indicadores claros; cards para notificaciones/avisos.
8. Tablas admin: `table-responsive`; acciones con botones consistentes.
9. Modales Bootstrap 5 para confirmaciones destructivas.
10. Verificar breakpoints (móvil primero cuando aplique).
11. Iconos Font Awesome (o equivalente) con significado, no decoración excesiva.
12. **Navegación móvil (SPEC 26):** implementar botón hamburguesa en navbar (&lt; lg) que abre/cierra el sidebar off-canvas.
13. **Navbar guest (SPEC 28):** en layout auth, barra horizontal con Inicio de sesión y Registro; skill `skills/navbar-guest/SKILL.md`.

### Navegación móvil — menú hamburguesa (obligatorio)

**SPEC:** `spec/26-navegacion-movil.md`

1. En `navbar.php` añadir botón `#sidebarToggle` con icono `bi-list` / `bi-x`, visible solo en `&lt;lg` (`d-lg-none`).
2. Sidebar con `id="app-sidebar"`; en móvil posición fija off-canvas (`transform: translateX(-100%)`).
3. Clase `.is-open` en sidebar (y opcionalmente `body.sidebar-open`) al abrir.
4. Backdrop `#sidebarBackdrop` clicable para cerrar.
5. JS: toggle, Escape, cerrar al navegar; actualizar `aria-expanded`.
6. Desktop (≥lg): sidebar fija; hamburguesa y backdrop ocultos.
7. Probar 390px y 1366px antes de marcar COMPLETED en la SPEC.

## Convenciones de código

- Bootstrap 5.x vía vendor local o CDN documentado en SPEC/deployment.
- Clases utilitarias Bootstrap + CSS propio mínimo (no pelear con el framework).
- Botones: `btn-primary` mapeado al azul institucional (override CSS).
- Cards para feed de notificaciones/avisos; destacar prioridad con borde/badge celeste/azul.
- No introducir temas púrpura genéricos ni dark mode por defecto.
- Evitar sobrecarga: menos chips, menos widgets, más claridad.
- Formularios con labels visibles, `invalid-feedback`, grupos consistentes.
- Mensajes flash: `alert-success`, `alert-danger`, `alert-warning`, `alert-info` temáticos.
- Accesibilidad básica: contraste, focus, `alt` en imágenes, labels asociados.
- Textos UI en español.

### Identidad visual (obligatoria)

- **Celeste + Azul + Blanco** como base.
- Sensación educativa e institucional.
- Marca/nombre del plantel visible en layout autenticado y login.
- Responsive: Desktop / Tablet / Mobile.

## Checklist de seguridad

- [ ] Escape de datos en todas las vistas.
- [ ] CSRF hidden en forms.
- [ ] No exponer datos sensibles en HTML comentado.
- [ ] Enlaces/botones respetan authz (y backend también).
- [ ] Assets externos solo de fuentes confiables/versionadas.
- [ ] Sin `javascript:` URLs con input de usuario.

## Checklist de pruebas

- [ ] Login y dashboard se ven correctos en 320px, 768px, 1200px+.
- [ ] Navbar/sidebar usables en móvil (hamburguesa + off-canvas según SPEC 26).
- [ ] Desktop: sidebar fija sin hamburguesa.
- [ ] Cards de notificación muestran prioridad/fecha/CTA.
- [ ] Tablas no rompen el viewport (scroll horizontal si hace falta).
- [ ] Modales de confirmación funcionan.
- [ ] Estados vacíos y de error son claros.
- [ ] Contraste texto/fondo aceptable sobre azul/celeste/blanco.
- [ ] Tipografía y botones consistentes entre módulos.

## Errores comunes a evitar

- Mezclar Bootstrap 4 y 5 (data-attributes distintos).
- Paletas ajenas a la institución (morado neón, dark glossy).
- Primer viewport saturado de widgets irrelevantes.
- Cards dentro de cards innecesarias en admin denso (usar solo cuando aporten).
- Botones sin jerarquía (todos `btn-primary`).
- Olvidar empty states.
- Inline styles dispersos en vez de tokens CSS.

## Criterio de done

Bootstrap UI está **done** cuando las pantallas del módulo usan el layout institucional celeste/azul/blanco, son responsive, reutilizan componentes, escapan datos, mantienen jerarquía visual clara y cumplen los criterios de `spec/06-ui-ux.md` y aceptación visual del módulo.
