# SPEC 03 — Requisitos No Funcionales (RNF)

**Proyecto:** Unidad Educativa Fiscomisional San Lorenzo (PortalInfor)  
**Metodología:** POLKDEV — SPEC → SKILL → CODE  
**Estado de implementación:** PENDING

---

## 1. Objetivo

Establecer requisitos no funcionales de rendimiento, seguridad, usabilidad, diseño responsive, mantenibilidad y compatibilidad con entorno XAMPP para el PortalInfor.

## 2. Alcance

### Incluye
- RNF medibles o verificables en QA.
- Restricciones de entorno local XAMPP.
- Criterios de calidad de código y documentación.

### No incluye
- SLA cloud / alta disponibilidad productiva (fuera de fase XAMPP local).
- CDN global obligatorio.

## 3. Actores

| Actor | Interés RNF |
|-------|-------------|
| Usuario final | Usabilidad, velocidad, responsive |
| ADMIN | Seguridad, auditoría, estabilidad |
| Desarrollador | Mantenibilidad, claridad MVC |
| QA | Criterios medibles |

## 4. Requisitos

### 4.1 Rendimiento — RNF-PERF

| ID | Requisito |
|----|-----------|
| RNF-PERF-01 | Listados paginados (default 10–20 ítems) |
| RNF-PERF-02 | Consultas con índices en FKs y campos de búsqueda (email, username, status) |
| RNF-PERF-03 | Tiempo de respuesta objetivo < 2s en operaciones comunes en localhost XAMPP |
| RNF-PERF-04 | Assets CSS/JS minificados o CDN Bootstrap; evitar bloquear render innecesariamente |
| RNF-PERF-05 | Uploads con límite configurable (ej. 5–10 MB por archivo) |

### 4.2 Seguridad — RNF-SEC

| ID | Requisito |
|----|-----------|
| RNF-SEC-01 | Cumplir controles de SPEC 05 y alineación OWASP Top 10 |
| RNF-SEC-02 | HTTPS recomendado en despliegue; en local HTTP aceptable con aviso |
| RNF-SEC-03 | Logs sin passwords ni tokens en claro |
| RNF-SEC-04 | Timeouts de sesión y regeneración de ID |
| RNF-SEC-05 | Rate limit básico en login |

### 4.3 Usabilidad — RNF-UX

| ID | Requisito |
|----|-----------|
| RNF-UX-01 | Textos UI en español claro institucional |
| RNF-UX-02 | Feedback inmediato (toast/alert) en AJAX |
| RNF-UX-03 | Formularios con labels, placeholders y mensajes de error por campo |
| RNF-UX-04 | Confirmación en acciones destructivas (desactivar, archivar, borrar adjunto) |
| RNF-UX-05 | Contador visible de notificaciones no leídas |

### 4.4 Responsive — RNF-RESP

| ID | Requisito |
|----|-----------|
| RNF-RESP-01 | Breakpoints Bootstrap 5: xs <576, sm ≥576, md ≥768, lg ≥992, xl ≥1200 |
| RNF-RESP-02 | Sidebar colapsable / offcanvas en < lg |
| RNF-RESP-03 | Tablas con scroll horizontal en móvil |
| RNF-RESP-04 | Touch-friendly: botones ≥ 40px altura aproximada |

### 4.5 Mantenibilidad — RNF-MAIN

| ID | Requisito |
|----|-----------|
| RNF-MAIN-01 | Código MVC por capas; sin SQL en vistas |
| RNF-MAIN-02 | SPEC + SKILL antes de CODE (POLKDEV) |
| RNF-MAIN-03 | Nombres consistentes ES/EN según convención del repo (clases EN, UI ES) |
| RNF-MAIN-04 | Migraciones versionadas y seeders reproducibles |
| RNF-MAIN-05 | README con instalación XAMPP |

### 4.6 Entorno XAMPP — RNF-XAMPP

| ID | Requisito |
|----|-----------|
| RNF-XAMPP-01 | Compatible PHP 8.x de XAMPP |
| RNF-XAMPP-02 | MySQL/MariaDB local; BD `ue_san_lorenzo` |
| RNF-XAMPP-03 | Rewrite Apache habilitado para front controller |
| RNF-XAMPP-04 | Rutas Windows-friendly (DIRECTORY_SEPARATOR / paths normalizados) |
| RNF-XAMPP-05 | Config vía `.env` leído en bootstrap |

## 5. Flujo funcional

No define flujos de negocio; condiciona la calidad de todos los flujos RF:
1. Request → debe responder dentro de umbral.
2. UI → debe adaptarse al viewport.
3. Error → debe registrarse sin filtrar secretos.

## 6. Reglas de negocio

1. Si un cambio degrada seguridad (RNF-SEC), no se fusiona.
2. Features P0 no se aceptan sin paginación cuando listan colecciones grandes.
3. Cualquier upload supera el límite ⇒ rechazo controlado, no 500 opaco.
4. En XAMPP, documentar VirtualHost o alias `http://localhost/PortalInfor/public`.

## 7. Estructura de datos

Configuración relevante (no tabla):

| Clave | Ejemplo | Uso |
|-------|---------|-----|
| `APP_DEBUG` | false | ocultar stack |
| `SESSION_LIFETIME` | 120 (min) | timeout |
| `LOGIN_MAX_ATTEMPTS` | 5 | brute force |
| `UPLOAD_MAX_MB` | 10 | archivos |
| `APP_URL` | http://localhost/PortalInfor/public | links |

## 8. Validaciones

- QA verifica checklist RNF en cada release de módulo.
- Lighthouse/manual responsive en 375px, 768px, 1280px.
- Prueba de carga ligera: 50 requests listado usuarios en local sin error 500.

## 9. Seguridad

Los RNF-SEC son mandatorios. Detalle operativo en SPEC 05. Headers mínimos: `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`.

## 10. Interfaz

- Contraste legible sobre fondos blancos/celestes.
- No depender solo del color para estados (íconos + texto).
- Skeleton/spinner en cargas AJAX > 300ms percibidos.

## 11. AJAX requerido

- Respuestas JSON compactas (sin volcar entidades enormes).
- Códigos HTTP correctos para que el front maneje UX de error.
- Evitar polling agresivo; preferir refresh manual o intervalo ≥ 60s para contador notificaciones.

## 12. Respuestas esperadas

| Condición | Esperado |
|-----------|----------|
| Listado OK | 200 < 2s local |
| Payload inválido | 400 con errors |
| Sin sesión | 401 |
| Debug off + exception | 500 genérico |

## 13. Manejo de errores

- `logs/app.log` rotativo o por fecha.
- Nivel: error/warning/info.
- En UI: mensaje amigable; detalle solo si `APP_DEBUG=true`.

## 14. Casos de prueba

| ID | Caso | Esperado |
|----|------|----------|
| RNF-T01 | Login 6 veces mal | Bloqueo / 429 |
| RNF-T02 | Viewport 375px dashboard | Sidebar offcanvas usable |
| RNF-T03 | Listar 100 usuarios | Paginación, sin timeout |
| RNF-T04 | APP_DEBUG=false + error | Sin stack trace HTML |
| RNF-T05 | Upload 15MB si max=10 | Rechazo |

## 15. Criterios de aceptación

- [ ] Checklist RNF-PERF/SEC/UX/RESP/MAIN/XAMPP verificado.
- [ ] Documentación de instalación XAMPP disponible.
- [ ] Sesión y uploads configurables.
- [ ] UI usable en móvil y escritorio.

## 16. Dependencias

- SPEC 01 (arquitectura), 05 (seguridad), 06 (UI).
- Extensiones PHP: fileinfo, mbstring, pdo_mysql.

## 17. SKILL requerida

- `skills/security/SKILL.md`
- `skills/bootstrap-ui/SKILL.md`
- `skills/deployment/SKILL.md`
- `skills/testing/SKILL.md`

## 18. Estado de implementación

**PENDING**
