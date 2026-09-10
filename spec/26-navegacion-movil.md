# SPEC 26 — Navegación móvil (menú hamburguesa)

**Proyecto:** Unidad Educativa Fiscomisional San Lorenzo (PortalInfor)  
**Metodología:** POLKDEV — SPEC → SKILL → CODE  
**Estado de implementación:** COMPLETED

---

## 1. Objetivo

En vista móvil/tablet, la barra de navegación superior debe incluir un **botón desplegable tipo hamburguesa** que abra y cierre el menú lateral (sidebar) sin ocupar el viewport de forma permanente.

## 2. Alcance

### Incluye
- Botón hamburguesa visible solo en breakpoints &lt; `lg` (992px).
- Sidebar oculta por defecto en móvil; se muestra como panel deslizable (off-canvas).
- Overlay/backdrop al abrir; cierre por botón, overlay o tecla Escape.
- Accesibilidad: `aria-expanded`, `aria-controls`, foco y etiqueta clara.

### No incluye
- App nativa.
- Cambiar la estructura de ítems del menú (solo presentación).

## 3. Actores

Todos los usuarios autenticados que usan layout `main`.

## 4. Requisitos

1. En desktop (≥992px): sidebar fija visible; hamburguesa **oculta**.
2. En móvil/tablet (&lt;992px): sidebar fuera de pantalla; hamburguesa **visible** en navbar.
3. Clic/tap en hamburguesa abre/cierra el menú.
4. Al abrir: `body` no debe hacer scroll de fondo (opcional recomendado).
5. Ítems del menú siguen filtrados por permisos.
6. Tras navegar a un enlace del sidebar en móvil, el menú se cierra.

## 5. Flujo funcional

1. Usuario en móvil carga layout autenticado → ve navbar con ícono ☰.
2. Pulsa hamburguesa → sidebar se desliza desde la izquierda + backdrop.
3. Selecciona opción → navega y el menú se cierra.
4. Pulsa backdrop / ✕ / Escape → menú se cierra.

## 6. Reglas de negocio

1. No duplicar menús: un solo sidebar reutilizado.
2. El botón no debe mostrarse en layout `auth` (login/registro).
3. Estado abierto no debe persistir tras recarga (por defecto cerrado).

## 7. Estructura de datos

No aplica persistencia. Estado UI en DOM:

- `html` o `#app-sidebar` con clase `.is-open`
- Botón `#sidebarToggle` con `aria-expanded="true|false"`

## 8. Validaciones

- Breakpoint alineado a Bootstrap `lg`.
- Contraste del ícono hamburguesa sobre fondo blanco de navbar.

## 9. Seguridad

Sin impacto de seguridad adicional (solo UI). No exponer rutas sin permiso (ya filtradas en sidebar).

## 10. Interfaz

- Botón: tres líneas (Bootstrap Icons `bi-list`) o animación a `bi-x` al abrir.
- Posición: extremo izquierdo de la navbar en móvil.
- Sidebar: mismo estilo institucional azul/celeste.

## 11. AJAX requerido

No obligatorio. JS vanilla o Bootstrap Offcanvas.

## 12. Respuestas esperadas

N/A (UI).

## 13. Manejo de errores

Si JS falla: sidebar puede quedar accesible al final del DOM (degradación: mostrar enlace “Menú” o sidebar estática mínima). Preferible: CSS + JS; sin JS el botón no abre (documentar).

## 14. Casos de prueba

| ID | Caso | Esperado |
|----|------|----------|
| NAV-M01 | Viewport 390px | Hamburguesa visible |
| NAV-M02 | Viewport 1366px | Hamburguesa oculta, sidebar fija |
| NAV-M03 | Abrir menú | Sidebar + backdrop visibles |
| NAV-M04 | Cerrar con backdrop | Menú cerrado |
| NAV-M05 | Clic en ítem | Navega y cierra |
| NAV-M06 | Escape | Cierra menú |

## 15. Criterios de aceptación

- [x] Botón hamburguesa en navbar móvil
- [x] Sidebar off-canvas funcional
- [x] Desktop sin regresiones
- [x] Accesible con teclado (Escape + aria)
- [x] SPEC y SKILL alineadas

## 16. Dependencias

- Layout `main.php`, partials `navbar.php`, `sidebar.php`
- `app/assets/css/app.css`, `app/assets/js/app.js`
- Bootstrap 5 / Bootstrap Icons

## 17. SKILL requerida

`skills/bootstrap-ui/SKILL.md` (sección navegación móvil / hamburguesa)

## 18. Estado de implementación

COMPLETED — botón hamburguesa, sidebar off-canvas, backdrop, Escape y cierre al navegar.
