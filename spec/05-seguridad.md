# SPEC 05 — Seguridad

**Proyecto:** Unidad Educativa Fiscomisional San Lorenzo (SISTEMA_G_TECNICO_SAN_LORENZO)  
**Metodología:** POLKDEV — SPEC → SKILL → CODE  
**Estado de implementación:** PENDING

---

## 1. Objetivo

Especificar los controles de seguridad obligatorios del SISTEMA_G_TECNICO_SAN_LORENZO: sesiones endurecidas, hashing de contraseñas, CSRF, XSS, prevención SQLi con PDO, headers HTTP y uploads seguros, alineados a buenas prácticas OWASP.

## 2. Alcance

### Incluye
- Autenticación segura y gestión de sesión.
- Protección de formularios y AJAX (CSRF).
- Codificación de salida (XSS).
- Acceso a datos solo con PDO prepared statements.
- Headers de seguridad.
- Pipeline de upload seguro.
- Rate limiting básico de login.

### No incluye
- WAF externo, pentest formal completo.
- 2FA (puede planearse como extensión futura).

## 3. Actores

| Actor | Responsabilidad |
|-------|-----------------|
| Todos los usuarios autenticados | Credenciales, sesión |
| ADMIN | Gestión de accesos y revisión de auditoría |
| Middleware/Services | Aplicar controles |
| Atacante (modelo de amenaza) | Fuerza bruta, SQLi, XSS, CSRF, upload malicious |

## 4. Requisitos

1. `password_hash($pass, PASSWORD_DEFAULT)` al crear/reset; `password_verify` en login.
2. Cookies de sesión: `HttpOnly`, `SameSite=Lax` (o Strict si compatible), `Secure` cuando HTTPS.
3. `session_regenerate_id(true)` tras login exitoso.
4. Token CSRF por sesión; validar en toda mutación.
5. Escape HTML en vistas (`htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`).
6. PDO con `ATTR_EMULATE_PREPARES => false`, prepared statements.
7. Headers: `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`.
8. Uploads: whitelist extensión + MIME fileinfo + rename aleatorio + storage fuera de `public/` + bloquear path traversal.
9. Timeout de sesión por inactividad.
10. Contador de intentos fallidos + `locked_until`.

## 5. Flujo funcional

### Login seguro
```text
POST credenciales + CSRF
→ validar CSRF
→ buscar user activo
→ si locked_until > now → 429
→ password_verify
→ fail: incrementar attempts; si >= N set lock
→ ok: reset attempts, regenerate session, audit login
```

### Request autenticado
```text
Middleware Session → usuario activo
→ CSRF si mutación
→ Permission middleware
→ Controller
```

### Upload
```text
Recibir archivo → tamaño OK → extensión whitelist
→ MIME real OK → no double extension peligrosa
→ stored_name = random(40).ext
→ mover a storage/uploads/YYYY/MM/
→ registrar attachments
→ nunca ejecutar ni servir como PHP
```

## 6. Reglas de negocio

1. Deny by default en autorización.
2. No revelar si el email/username existe en mensajes de login genéricos (“Credenciales inválidas”).
3. Logs de auditoría para cambios de roles, reset password, publicación, uploads.
4. Descarga de archivos solo por ID + auth + permiso; path construido en servidor.
5. Deshabilitar listado de directorios Apache.
6. `.env` fuera de document root / no servido.

## 7. Estructura de datos

Campos de seguridad en BD (SPEC 04):
- `users.password`, `failed_login_attempts`, `locked_until`, `status`
- `sessions.*`
- `audit_logs.*`
- `attachments.disk_path`, `checksum_sha256`

Sesión PHP (claves sugeridas):
- `user_id`, `roles`, `permissions`, `csrf_token`, `last_activity`

## 8. Validaciones

| Control | Validación |
|---------|------------|
| Password | min 8, complejidad recomendada (letra+número) |
| CSRF | token timing-safe compare |
| Upload ext | solo lista blanca SPEC 12 |
| MIME | finfo_file coincide con mapa permitido |
| Path | `realpath` dentro de base storage; rechazar `..` |
| Input | longitud máxima; strip de null bytes |

## 9. Seguridad

Esta SPEC **es** el núcleo de seguridad. Controles mínimos OWASP mapeados:

| Riesgo | Control SISTEMA_G_TECNICO_SAN_LORENZO |
|--------|---------------------|
| Injection | PDO prepared |
| Broken Auth | hash, sesión, lockout |
| XSS | escape salida, CSP básica opcional |
| CSRF | tokens |
| Misconfig | document root public/, debug off |
| Sensitive data | no logs de secrets |
| Access control | RBAC middleware |
| Upload malware | whitelist + MIME + fuera public |

## 10. Interfaz

- Formularios con hidden `_csrf`.
- Meta tag `csrf-token` para AJAX.
- Mensajes de error genéricos en login.
- Avisos de sesión expirada → redirect login.

## 11. AJAX requerido

- Header `X-CSRF-TOKEN` o campo `_csrf` en body JSON/form.
- Respuestas 401/403/419 diferenciadas para que el front redirija o muestre modal.
- No devolver stack traces ni SQLSTATE al cliente.

## 12. Respuestas esperadas

| Evento | HTTP | Body |
|--------|------|------|
| CSRF inválido | 419 | `{ success:false, message:"Token inválido" }` |
| No auth | 401 | mensaje sesión |
| No permiso | 403 | acceso denegado |
| Locked | 429 | cuenta bloqueada temporalmente |
| Login ok | 200 | redirect url / data user safe |

## 13. Manejo de errores

- Fallos de seguridad se registran en `audit_logs` o `logs/security.log` (intentos, CSRF fail, upload reject).
- Al usuario: mensaje corto sin detalle de exploit.
- En desarrollo (`APP_DEBUG`): más detalle solo para ADMIN local.

## 14. Casos de prueba

| ID | Caso | Esperado |
|----|------|----------|
| SEC-01 | SQL en campo login | No inyección; login fail |
| SEC-02 | Script en título aviso | Escapado en vista |
| SEC-03 | POST sin CSRF | 419 |
| SEC-04 | Cookie HttpOnly | No legible por JS |
| SEC-05 | Upload shell.php.jpg | Rechazo MIME/ext |
| SEC-06 | `../../etc/passwd` en download | 404/403 |
| SEC-07 | 5 logins fallidos | lock temporal |
| SEC-08 | password en audit log | ausente |

## 15. Criterios de aceptación

- [ ] password_hash/verify en uso.
- [ ] CSRF en todas las mutaciones web/AJAX.
- [ ] PDO prepared en todos los queries.
- [ ] Headers de seguridad activos.
- [ ] Uploads fuera de public + rename + MIME.
- [ ] Session regenerate + timeout + lockout básico.
- [ ] Casos SEC-01..08 pasan.

## 16. Dependencias

- SPEC 01, 04, 07, 12.
- Extensiones: openssl, fileinfo, session.
- Apache: AllowOverride para .htaccess.

## 17. SKILL requerida

- `skills/security/SKILL.md`
- `skills/owasp/SKILL.md`
- `skills/authentication/SKILL.md`
- `skills/file-upload/SKILL.md`

## 18. Estado de implementación

**PENDING**
