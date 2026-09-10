# SPEC 28 — Navbar guest horizontal (Inicio de sesión / Registro)

**Proyecto:** Unidad Educativa Fiscomisional San Lorenzo (SISTEMA_G_TECNICO_SAN_LORENZO)  
**Metodología:** POLKDEV — SPEC → SKILL → CODE  
**Estado de implementación:** COMPLETED

---

## 1. Objetivo

Definir una **barra de navegación horizontal** para visitantes no autenticados (layout guest/auth), con marca institucional a la izquierda y acciones **Inicio de sesión** y **Registro** a la derecha, usable y responsive en desktop, tablet y móvil.

## 2. Alcance

### Incluye
- Partial reutilizable de navbar guest (p. ej. `partials/navbar-guest.php`).
- Integración en layout guest/auth (`layouts/auth.php` o equivalente).
- Enlaces a `/login` y `/register`.
- Comportamiento responsive: barra horizontal en desktop; colapso con toggler (hamburguesa) en breakpoints &lt; `lg`.
- Estado activo visual según ruta actual (login vs registro).
- Paleta institucional celeste/azul/blanco (SPEC 06).
- Accesibilidad básica (`aria-label`, `aria-expanded`, foco visible).

### No incluye
- Navbar autenticada (`navbar.php` + sidebar) — ver SPEC 26.
- Lógica de autenticación (login/registro POST) — ver SPEC 07.
- Landing pública con más ítems de menú (puede extenderse en SPEC futura).
- App nativa.

## 3. Actores

| Actor | Uso de la navbar |
|-------|------------------|
| Visitante (guest) | Ve marca + Inicio de sesión + Registro |
| Usuario autenticado | No debe ver esta navbar (redirigido por middleware `guest` / home) |

## 4. Requisitos

1. Navbar horizontal fija o sticky en la parte superior del viewport guest.
2. Izquierda: marca institucional (nombre del portal y/o escudo reducido).
3. Derecha (desktop ≥992px): botones/enlaces **Inicio de sesión** y **Registro** visibles en fila.
4. Móvil/tablet (&lt;992px): toggler visible; al expandir, aparecen los mismos dos enlaces (sin sidebar).
5. En `/login`, resaltar **Inicio de sesión** como activo; en `/register`, resaltar **Registro**.
6. Textos UI en español.
7. Bootstrap 5 (`navbar`, `navbar-expand-lg`, `collapse`).
8. No mostrar hamburguesa de sidebar (SPEC 26); este toggler solo colapsa los enlaces guest.
9. Si el registro institucional estuviera deshabilitado por configuración futura, ocultar el enlace Registro (flag documentado; default: visible).

## 5. Flujo funcional

```text
1. Visitante abre /login o /register
2. Layout auth incluye partial navbar-guest
3. Usuario ve marca + acciones de acceso
4. Clic "Inicio de sesión" → GET /login
5. Clic "Registro" → GET /register
6. En viewport < lg: abre toggler → ve los mismos enlaces → navega → menú colapsado
```

## 6. Reglas de negocio

1. Solo en contexto **guest**; no mezclar con menú de usuario autenticado.
2. Un solo partial guest; no duplicar markup en cada vista auth.
3. Los enlaces no ejecutan POST; solo navegación GET.
4. Marca visible y legible; no debe quedar solo el toggler sin identidad.
5. Contraste suficiente sobre fondo de navbar (azul institucional o blanco según tema).
6. No persistir estado abierto del collapse tras recarga (cerrado por defecto).

## 7. Estructura de datos

Sin persistencia en BD. Estado UI:

| Elemento | Identificador sugerido |
|----------|------------------------|
| Nav | `#guestNavbar` / clase `.guest-navbar` |
| Toggler | `#guestNavToggle` |
| Collapse | `#guestNavCollapse` |
| Link login | `.nav-link` hacia `/login` + clase `.active` si ruta actual |
| Link registro | `.nav-link` o `.btn` hacia `/register` + `.active` si aplica |

Detección de ruta activa: comparar path actual con `/login` y `/register` (helper de request o variable de vista).

## 8. Validaciones

- Breakpoint de colapso alineado a Bootstrap `lg` (992px), coherente con SPEC 06/26.
- Enlaces apuntan a rutas reales definidas en `routes/web.php`.
- Sin scroll horizontal indeseado en 320px–390px.
- `aria-controls` del toggler apunta al `id` del collapse.

## 9. Seguridad

- Solo URLs internas vía helper `url()`; sin `javascript:` ni destinos abiertos.
- Escape de nombre de marca/configuración (`e()`).
- No exponer datos de sesión ni permisos en este partial.
- CSRF no aplica a los enlaces GET; los formularios de login/registro siguen SPEC 07.

## 10. Interfaz

### Desktop (≥ lg)
```text
┌─────────────────────────────────────────────────────────────┐
│ [Escudo] UEF San Lorenzo / SISTEMA_G_TECNICO_SAN_LORENZO    [Inicio sesión] [Registro] │
└─────────────────────────────────────────────────────────────┘
```

### Móvil (&lt; lg)
```text
┌────────────────────────────────────┐
│ [Escudo] Marca…              [☰]   │
└────────────────────────────────────┘
         ↓ al abrir
┌────────────────────────────────────┐
│ Inicio de sesión                   │
│ Registro                           │
└────────────────────────────────────┘
```

### Estilo
- Fondo: azul institucional (`--pi-blue-700` / `--brand-primary`) o blanco con borde inferior; coherente con `auth-body`.
- **Inicio de sesión**: estilo outline o link claro.
- **Registro**: botón primario (CTA) cuando no está activo; inverso o muted si la página actual es registro.
- Iconos opcionales Bootstrap Icons (`bi-box-arrow-in-right`, `bi-person-plus`) sin saturar.

### Layout
- El contenido del formulario auth queda **debajo** de la navbar (no tapado).
- Padding-top del body/content si navbar es `fixed-top`.

## 11. AJAX requerido

No obligatorio. Colapso vía Bootstrap Collapse JS ya cargado en layout auth.

## 12. Respuestas esperadas

N/A (navegación HTML). Tras clic: carga de vista login o registro con navbar presente y estado activo correcto.

## 13. Manejo de errores

| Situación | Comportamiento |
|-----------|----------------|
| JS deshabilitado | Enlaces deben seguir accesibles: preferir no depender solo del collapse; degradación: mostrar links apilados o siempre visibles bajo la marca |
| Ruta 404 de asset | Navbar usable con CSS Bootstrap base |
| Usuario ya autenticado | Middleware guest redirige; no renderiza esta navbar |

## 14. Casos de prueba

| ID | Caso | Esperado |
|----|------|----------|
| NG-01 | GET /login en 1366px | Navbar horizontal; ambos enlaces visibles; Login activo |
| NG-02 | GET /register en 1366px | Registro activo; Login no activo |
| NG-03 | Viewport 390px | Toggler visible; enlaces en collapse |
| NG-04 | Abrir toggler y ir a Registro | Navega a /register; menú cerrado tras carga |
| NG-05 | Clic Inicio de sesión | GET /login |
| NG-06 | Usuario autenticado visita /login | Redirect dashboard; sin navbar guest |
| NG-07 | Contraste marca/enlaces | Texto legible sobre fondo navbar |
| NG-08 | Teclado | Toggler y enlaces enfocables; Escape cierra collapse (comportamiento Bootstrap) |

## 15. Criterios de aceptación

- [x] Existe partial `navbar-guest` (o nombre equivalente documentado).
- [x] Layout auth/guest incluye la navbar.
- [x] Enlaces **Inicio de sesión** y **Registro** funcionan.
- [x] Responsive: horizontal en lg+; colapsable en &lt;lg.
- [x] Estado activo según ruta.
- [x] Paleta institucional aplicada.
- [x] No rompe ni duplica la navbar autenticada (SPEC 26).
- [x] Casos NG-01 a NG-08 verificados.
- [x] SPEC y SKILL alineadas; estado COMPLETED solo tras código + pruebas.

## 16. Dependencias

- `spec/06-ui-ux.md` (identidad visual, breakpoints)
- `spec/07-autenticacion.md` (rutas /login, /register)
- `spec/26-navegacion-movil.md` (no confundir con hamburguesa de sidebar)
- Layout `auth.php`, rutas guest, Bootstrap 5, tokens CSS institucionales

## 17. SKILL requerida

`skills/navbar-guest/SKILL.md`

Complementarias: `skills/bootstrap-ui/SKILL.md`, `skills/authentication/SKILL.md`

## 18. Estado de implementación

COMPLETED — partial `navbar-guest.php`, layout `auth.php`, estilos `.guest-navbar` en `app.css`.
