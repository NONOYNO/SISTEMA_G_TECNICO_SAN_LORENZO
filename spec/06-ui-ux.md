# SPEC 06 — UI / UX

**Proyecto:** Unidad Educativa Fiscomisional San Lorenzo (SISTEMA_G_TECNICO_SAN_LORENZO)  
**Metodología:** POLKDEV — SPEC → SKILL → CODE  
**Estado de implementación:** PENDING

---

## 1. Objetivo

Definir la identidad visual y patrones de interfaz del SISTEMA_G_TECNICO_SAN_LORENZO: paleta celeste/azul/blanco, uso de Bootstrap 5, cards, navbar, sidebar y comportamiento responsive para una experiencia institucional clara y usable.

## 2. Alcance

### Incluye
- Tokens de color y tipografía base.
- Layout autenticado y guest.
- Componentes: navbar, sidebar, cards, tablas, formularios, alerts/toasts.
- Breakpoints y reglas responsive.
- Estados vacíos y de carga.

### No incluye
- App móvil nativa.
- Design system Figma obligatorio (puede añadirse después).

## 3. Actores

Todos los roles autenticados y visitante (login). Cada rol ve menú filtrado por permisos, misma base visual.

## 4. Requisitos

1. Bootstrap 5 como framework CSS/JS principal.
2. Paleta institucional celeste / azul / blanco.
3. Cards para notificaciones, avisos y KPIs.
4. Navbar superior con marca, contador notificaciones, menú usuario.
5. Sidebar con ítems de navegación; offcanvas en móvil.
6. Responsive en breakpoints Bootstrap.
7. Iconografía Font Awesome (o Bootstrap Icons) consistente.
8. Textos UI en español.

## 5. Flujo funcional

1. Usuario no autenticado → layout guest (centrado, branding).
2. Login OK → layout app (navbar + sidebar + content).
3. Navegación sidebar cambia content; AJAX actualiza fragments cuando aplique.
4. Acciones → toast éxito/error sin romper layout.

## 6. Reglas de negocio

1. Ítems de menú sin permiso no se muestran (además del 403 servidor).
2. Prioridad alta en cards: borde/badge distintivo (no solo color).
3. No usar más de un H1 por vista.
4. Mantener jerarquía visual: marca institucional visible en navbar.
5. Evitar saturación: máximo 3–4 colores de acento además de neutros.

## 7. Estructura de datos

Tokens CSS sugeridos (`public/assets/css/app.css`):

```css
:root {
  --pi-blue-900: #0D47A1;
  --pi-blue-700: #1565C0;
  --pi-blue-500: #0288D1;
  --pi-sky-400: #4FC3F7;
  --pi-sky-100: #E1F5FE;
  --pi-white: #FFFFFF;
  --pi-gray-100: #F5F7FA;
  --pi-gray-700: #455A64;
  --pi-danger: #C62828;
  --pi-success: #2E7D32;
  --pi-warning: #F9A825;
}
```

Componentes de vista:
- `layouts/app.php`, `layouts/guest.php`
- `partials/navbar.php`, `sidebar.php`, `flash.php`, `pagination.php`
- Cards de módulo en views específicas

## 8. Validaciones

- Contraste texto/fondo legible (evitar celeste claro sobre blanco sin contraste).
- Formularios: campos required marcados; errores bajo el input.
- Imágenes con `alt` descriptivo.
- Focus visible en controles teclado.

## 9. Seguridad

- Escape de todo contenido dinámico en cards/tablas.
- No renderizar HTML de usuario sin sanitizar (avisos: preferir texto o HTML allowlist estricto).
- CSRF hidden en forms UI.

## 10. Interfaz

### Navbar
- Logo/nombre: “UEF San Lorenzo” / SISTEMA_G_TECNICO_SAN_LORENZO.
- Campana notificaciones + badge count.
- Dropdown: perfil, cambiar password, logout.

### Sidebar
- Secciones: Dashboard, Avisos, Notificaciones, Usuarios, Roles, Archivos, Auditoría, Reportes (según permiso).
- Item activo resaltado con `--pi-blue-700` / fondo `--pi-sky-100`.

### Cards
- Cabecera: título + badge prioridad/estado.
- Cuerpo: descripción corta.
- Footer: fecha, acciones (ver, marcar leída, descargar).

### Login
- Fondo degradado suave azul→celeste o patrón sutil + card blanca centrada.
- Campos usuario/correo y password; link registro si habilitado.

### Breakpoints
| Breakpoint | Comportamiento |
|------------|----------------|
| <576px | Sidebar offcanvas; stacks verticales |
| ≥768px | Tablas usables; 2 columnas en forms |
| ≥992px | Sidebar fija visible |
| ≥1200px | Content max-width contenedor cómodo |

## 11. AJAX requerido

- Actualización badge notificaciones.
- Submit forms con feedback toast.
- Filtros de listados sin recargar página completa (opcional P1).
- Spinners en botones durante request (`disabled` + spinner Bootstrap).

## 12. Respuestas esperadas

- Carga de layout sin CLS severo (reservar alto badge).
- Toast 3–5s auto-hide en éxitos.
- Errores de validación inline + resumen opcional.

## 13. Manejo de errores

| UI state | Presentación |
|----------|--------------|
| Vacío | Empty state con ícono + CTA |
| Red error | Alert danger + reintentar |
| 403 | Vista “Acceso denegado” amable |
| 404 | Vista no encontrada |

## 14. Casos de prueba

| ID | Caso | Esperado |
|----|------|----------|
| UI-01 | Login en 375px | Form usable sin scroll horizontal |
| UI-02 | Sidebar < lg | Offcanvas abre/cierra |
| UI-03 | Card prioridad high | Badge visible + texto |
| UI-04 | Flash éxito | Toast/alert se muestra |
| UI-05 | Menú DOCENTE | No ve Usuarios/Roles |
| UI-06 | Contraste título sobre sky-100 | Legible |

## 15. Criterios de aceptación

- [ ] Paleta celeste/azul/blanco aplicada en layout base.
- [ ] Bootstrap 5 integrado.
- [ ] Navbar + sidebar + cards implementados según patrones.
- [ ] Responsive verificado en sm/md/lg.
- [ ] Menú filtrado por permisos.
- [ ] Feedback AJAX visible.

## 16. Dependencias

- Bootstrap 5 CSS/JS.
- Icon library.
- SPEC 00, 01, 11 (cards notificaciones).

## 17. SKILL requerida

- `skills/bootstrap-ui/SKILL.md`
- `skills/ajax/SKILL.md`

## 18. Estado de implementación

**PENDING**
