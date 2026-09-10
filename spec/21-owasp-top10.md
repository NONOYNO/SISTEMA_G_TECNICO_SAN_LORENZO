# SPEC 21 — OWASP Top 10 (mitigaciones del sistema)

**Proyecto:** Unidad Educativa Fiscomisional San Lorenzo  
**Stack:** PHP 8 + MySQL + PDO + MVC + Bootstrap 5 + AJAX + XAMPP  
**Módulo:** Seguridad OWASP Top 10  
**Estado:** PENDING

---

## 1. Objetivo

Definir mitigaciones concretas A01–A10 aplicables al portal informativo de la Unidad Educativa Fiscomisional San Lorenzo, mapeadas a controles técnicos en PHP/MySQL/MVC y a pruebas verificables.

## 2. Alcance

**Incluye:**
- Controles por cada categoría OWASP Top 10 (2021).
- Requisitos de implementación y verificación.
- Relación con auditoría, auth, archivos y configuración XAMPP.

**Excluye:**
- Certificación formal ASVS completa.
- WAF comercial obligatorio.

## 3. Actores

| Actor | Rol |
|-------|-----|
| Desarrollador | Implementa controles |
| ADMIN | Configura y revisa logs |
| QA | Ejecuta casos SEC-* |
| Atacante (modelo) | Usuario malicioso autenticado o anónimo |

## 4. Requisitos

Implementar y verificar mitigaciones A01–A10 listadas en las secciones 6–7 y checklist de aceptación.

## 5. Flujo funcional

```text
Diseño seguro (SPEC)
  → Controles en código (middleware, PDO, CSRF, validación)
  → Pruebas seguridad (spec/20)
  → Monitoreo audit_logs + security.log
  → Corrección continua
```

## 6. Reglas de negocio / Mitigaciones A01–A10

### A01 — Broken Access Control
- Middleware de autenticación en rutas privadas.
- Policies/permisos RBAC en **backend** por acción.
- Evitar IDOR: verificar ownership/audiencia en avisos, archivos, notificaciones.
- Denegar por defecto; roles no escalables vía POST.
- No usar solo `hidden`/CSS para ocultar admin.
- Prohibir listar directorios Apache.

### A02 — Cryptographic Failures
- `password_hash` / `password_verify` (bcrypt/argon2 según PHP).
- HTTPS recomendado en despliegues no locales; en XAMPP local documentar limitación.
- Secretos en `.env` fuera de webroot / no versionar `.env`.
- No almacenar contraseñas ni tokens en `audit_logs`.
- Cookies de sesión: `HttpOnly`, `Secure` (si HTTPS), `SameSite=Lax/Strict`.

### A03 — Injection
- **SQL:** solo PDO prepared statements; cero concatenación de input.
- **XSS:** escape `htmlspecialchars` en vistas; sanitizar HTML rico.
- **Command injection:** no usar `exec`/`shell_exec` con input; backups con rutas fijas.
- Validar tipos (int, enum) antes de query.

### A04 — Insecure Design
- SPEC-first + threat model básico por módulo.
- Principio de mínimo privilegio en seeders.
- Límites de tamaño upload y rate limit login.
- Separar ambientes local/test/prod.
- Flujos destructivos con confirmación + CSRF + auditoría.

### A05 — Security Misconfiguration
- `display_errors=Off` en producción; logs a archivo.
- `.env`, `/database`, `/storage`, `/logs` no públicos.
- Deshabilitar listado de directorios.
- Runner migraciones/seeders web off en prod.
- Headers: `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`; CSP cuando viable.
- Quitar credenciales por defecto innecesarias.

### A06 — Vulnerable and Outdated Components
- Inventario de assets Bootstrap/Font Awesome/PHP libs en `docs/dependencies.md`.
- No incluir librerías abandonadas.
- Actualizar XAMPP/PHP cuando haya CVE críticas.
- Subresource integrity si se usan CDN (preferir vendor local).

### A07 — Identification and Authentication Failures
- Política de contraseñas fuerte.
- Regenerar session ID post-login.
- Logout invalida sesión servidor.
- Timeout de inactividad.
- Bloqueo/backoff tras N fallos de login.
- Mensajes de error de login genéricos (no “email no existe”).
- Reset password seguro (token tiempo limitado) si se implementa.

### A08 — Software and Data Integrity Failures
- Validar integridad MIME/extensión de uploads.
- No deserializar input de usuario (`unserialize` prohibido con data externa).
- CSRF en cambios de estado.
- Despliegue solo de artefactos revisados; sin edición web de PHP.
- Verificar que adjuntos no sean ejecutables por Apache.

### A09 — Security Logging and Monitoring Failures
- Tabla `audit_logs` + `logs/security.log`.
- Eventos: login OK/FAIL, logout, CRUD sensible, permisos, upload/download, publish.
- Sin datos sensibles en logs.
- ADMIN puede consultar auditoría (`spec/22-auditoria.md`).
- Alertas simples: picos de LOGIN_FAILED (log).

### A10 — Server-Side Request Forgery (SSRF)
- No implementar fetch de URLs arbitrarias del usuario.
- Si hay avatares remotos o webhooks futuros: allowlist de dominios.
- Deshabilitar redirect HTTP ciego en cualquier cliente interno.
- Validar que módulos de backup/reporte no acepten paths/URLs libres.

## 7. Estructura de datos

### Evidencias de control
| Control | Artefacto |
|---------|-----------|
| AuthZ | middleware + permissions |
| Crypto | password hashes en `users` |
| Logs | `audit_logs`, `security.log` |
| CSRF | tokens de sesión |
| Uploads | `attachments` + storage fuera de public |

## 8. Validaciones

Checklist de verificación por release:
- [ ] Ninguna query concatenada con input
- [ ] Todas las mutaciones con CSRF
- [ ] Pruebas SEC-001…SEC-005 PASS
- [ ] Uploads ejecutables rechazados
- [ ] Headers de seguridad activos
- [ ] `.env` no accesible vía HTTP

## 9. Seguridad

Este documento **es** la línea base de seguridad. Toda excepción debe documentarse en `docs/security-exceptions.md` con fecha y responsable.

## 10. Interfaz

- Mensajes de error genéricos al usuario.
- ADMIN: pantalla de auditoría y (opcional) resumen de fallos de login.
- No mostrar stack traces.

## 11. AJAX requerido

Endpoints AJAX deben aplicar **los mismos** controles:
- AuthN + AuthZ + CSRF + validación + logging.
- Respuestas uniformes sin filtrar información interna.

Pruebas AJAX de seguridad:
- POST sin CSRF → rechazo.
- DOCENTE llama `/ajax/admin/users` → 403.
- Payload XSS en campos JSON → almacenado escapado / sanitizado.

## 12. Respuestas esperadas

**Denegación uniforme:**
```json
{ "success": false, "message": "No autorizado", "code": "FORBIDDEN" }
```

**Login fallido genérico:**
```json
{ "success": false, "message": "Credenciales inválidas" }
```

## 13. Manejo de errores

- Errores de seguridad → `security.log` + opcional `audit_logs`.
- No distinguir en UI entre “sin permiso” y “no existe” cuando habilite enumeración (archivos).
- Fallo de logging no debe romper el request principal (best-effort + fallback file).

## 14. Casos de prueba

| ID | Categoría | Caso | Esperado |
|----|-----------|------|----------|
| OWASP-A01-01 | A01 | IDOR file | 403 |
| OWASP-A02-01 | A02 | Password DB | hash |
| OWASP-A03-01 | A03 | SQLi | sin bypass |
| OWASP-A03-02 | A03 | XSS reflected/stored | escaped |
| OWASP-A04-01 | A04 | Login rate | throttle |
| OWASP-A05-01 | A05 | GET /.env | 403/404 |
| OWASP-A06-01 | A06 | Inventario deps | documentado |
| OWASP-A07-01 | A07 | Session fix | ID regenerado |
| OWASP-A08-01 | A08 | Upload .phtml | reject |
| OWASP-A09-01 | A09 | Login fail logged | sí |
| OWASP-A10-01 | A10 | URL param fetch | no existe / bloqueado |

## 15. Criterios de aceptación

- [ ] Mitigaciones A01–A10 implementadas o N/A justificado.
- [ ] Suite seguridad mínima PASS.
- [ ] Archivos y auth endurecidos.
- [ ] Auditoría operativa.
- [ ] Configuración XAMPP endurecida según `spec/24`.
- [ ] SKILL owasp aplicada antes de cerrar seguridad.

## 16. Dependencias

- `spec/05-seguridad.md`, `spec/07-autenticacion.md`, `spec/09-roles-permisos.md`, `spec/12-archivos.md`, `spec/20-pruebas.md`, `spec/22-auditoria.md`
- Skills: `owasp`, `security`, `authentication`, `authorization`

## 17. SKILL requerida

```text
skills/owasp/SKILL.md
```

## 18. Estado de implementación

**PENDING**
