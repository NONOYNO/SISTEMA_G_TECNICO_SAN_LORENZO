# SPEC 07 — Autenticación

**Proyecto:** Unidad Educativa Fiscomisional San Lorenzo (SISTEMA_G_TECNICO_SAN_LORENZO)  
**Metodología:** POLKDEV — SPEC → SKILL → CODE  
**Estado de implementación:** COMPLETED (registro: active + DOCENTE + auto-login)

---

## 1. Objetivo

Especificar el módulo de autenticación: login con usuario o correo + password, logout, timeout de sesión, regeneración de session ID, protección brute-force básica y CSRF.

## 2. Alcance

### Incluye
- Login / logout.
- Registro público con rol DOCENTE, cuenta `active` y auto-login.
- Gestión de sesión autenticada.
- Lockout temporal por intentos fallidos.
- CSRF en login, registro y endpoints asociados.
- Redirect post-login / post-registro al dashboard.

### No incluye
- OAuth / SSO institucional.
- Recuperación de password por email (puede ser extensión; reset manual por ADMIN en SPEC 08).
- 2FA.

## 3. Actores

| Actor | Acciones |
|-------|----------|
| Visitante | Login (y registro si RF-REG activo) |
| Usuario activo | Logout, uso de sesión |
| Usuario locked/inactive | Login denegado |
| ADMIN | Desbloqueo indirecto (reactivar / reset attempts vía usuarios) |

## 4. Requisitos

1. Identificador de login: **username o email** + password.
2. Solo `status = active` puede iniciar sesión.
3. Logout destruye sesión servidor y cookie.
4. Timeout por inactividad (`SESSION_LIFETIME` minutos).
5. `session_regenerate_id(true)` en login OK.
6. Tras N fallos (`LOGIN_MAX_ATTEMPTS`, default 5): set `locked_until` (ej. +15 min).
7. CSRF obligatorio en POST login y POST registro.
8. Auditoría de login success/fail/logout y USER_REGISTERED.
9. AJAX login opcional además de form clásico.
10. Registro: `status=active`, rol DOCENTE, auto-login (RF-REG-02/03/05).

## 5. Flujo funcional

### Login
```text
1. GET /login → formulario + csrf
2. POST /login { login, password, _csrf }
3. Validar CSRF y campos
4. Buscar user WHERE (email=:x OR username=:x)
5. Si no existe / password mal / inactive → fail genérico (+ attempts si user existe)
6. Si locked_until > now → 429
7. password_verify OK → regenerate session, guardar user_id/roles/permissions, last_login_at, clear attempts
8. Redirect /dashboard (o JSON con redirect)
```

### Registro (público)
```text
1. GET /register → formulario + csrf
2. POST /register { first_name, last_name, email, username, password, password_confirmation, _csrf }
3. Validar CSRF, unicidad email/username y reglas de password
4. Crear usuario con status = active
5. Asignar rol sistema DOCENTE (único rol por defecto; nunca ADMIN/RECTOR/VICERRECTOR desde el form)
6. Auditar USER_REGISTERED (+ rol)
7. Iniciar sesión automáticamente (mismo flujo de sesión que login OK: regenerate + roles/permissions)
8. Redirect /dashboard
```

### Logout
```text
POST /logout + CSRF → audit → session_destroy → redirect /login
```

### Timeout
```text
Cada request: si now - last_activity > lifetime → destroy → login con flash "Sesión expirada"
Actualizar last_activity si OK
```

## 6. Reglas de negocio

1. Mensaje único de fallo: “Credenciales inválidas” (no distinguir email vs password).
2. Usuario `pending` o `inactive` no autentica vía login.
3. Sesión no reutiliza ID previo al login.
4. Permisos se cargan al login y pueden refrescarse al cambiar roles (re-login o reload permissions).
5. Rutas guest (`/login`, `/register`) redirigen a dashboard si ya hay sesión.
6. **Registro público:** estado inicial `active`; rol por defecto **DOCENTE**; auto-login post-registro.
7. El formulario de registro **no** permite elegir rol ni estado (anti privilege escalation).
8. Si el rol DOCENTE no existe en BD, el registro falla de forma controlada (no crear usuario huérfano sin rol).

## 7. Estructura de datos

**Entrada login:**
```json
{
  "login": "jperez o jperez@uel.edu.ec",
  "password": "********",
  "_csrf": "..."
}
```

**Sesión:**
- `user_id`, `username`, `full_name`, `roles[]`, `permissions[]`, `csrf_token`, `last_activity`

**BD:** `users`, `sessions` (opcional), `audit_logs`

## 8. Validaciones

| Campo | Regla |
|-------|-------|
| login | required, max 150 |
| password | required, max 255 |
| _csrf | required, match sesión |

Sanitizar login trim; no alterar password más allá de string.

## 9. Seguridad

- Cumple SPEC 05 íntegramente para este módulo.
- Rate limit: lockout en BD + opcional throttle por IP en sesión/archivo.
- No cachear páginas de login con datos sensibles (`Cache-Control: no-store` en respuestas auth).

## 10. Interfaz

- Vista `auth/login.php` en layout guest.
- Campos: Identificador, Contraseña, botón “Ingresar”.
- Alertas flash para expiración / logout / lock.
- Opción “mostrar password” (checkbox) opcional UX.

## 11. AJAX requerido

| Endpoint | Método | Descripción |
|----------|--------|-------------|
| `/ajax/auth/login` | POST | Login JSON |
| `/ajax/auth/logout` | POST | Logout JSON |
| `/ajax/auth/session` | GET | Ping sesión (alive / expired) |

## 12. Respuestas esperadas

**Login OK (200):**
```json
{
  "success": true,
  "message": "Bienvenido",
  "data": { "redirect": "/dashboard", "user": { "id": 1, "username": "admin" } },
  "errors": {}
}
```

**Login fail (401):**
```json
{
  "success": false,
  "message": "Credenciales inválidas",
  "data": null,
  "errors": {}
}
```

**Locked (429):**
```json
{
  "success": false,
  "message": "Cuenta temporalmente bloqueada. Intente más tarde.",
  "data": { "locked_until": "2026-09-08 16:10:00" },
  "errors": {}
}
```

## 13. Manejo de errores

| Error | Acción |
|-------|--------|
| CSRF | 419 + recargar form |
| Validación | 400 errors |
| BD caída | 500 genérico + log |
| Sesión expirada mid-AJAX | 401 → front a /login |

## 14. Casos de prueba

| ID | Caso | Esperado |
|----|------|----------|
| AUTH-01 | Login username OK | Dashboard |
| AUTH-02 | Login email OK | Dashboard |
| AUTH-03 | Password incorrecta | Mensaje genérico |
| AUTH-04 | Usuario inactive | Denegado |
| AUTH-05 | 5 fallos | Lock 429 |
| AUTH-06 | Logout | No acceso a /dashboard |
| AUTH-07 | Inactividad > lifetime | Redirect login |
| AUTH-08 | POST login sin CSRF | 419 |
| AUTH-09 | Session ID cambia tras login | Diferente al pre-login |
| AUTH-10 | Registro OK | Usuario active + rol DOCENTE |
| AUTH-11 | Registro OK | Sesión iniciada y redirect /dashboard |
| AUTH-12 | Registro no envía role=ADMIN | Ignorado; solo DOCENTE |

## 15. Criterios de aceptación

- [ ] Login con username o email funcional.
- [ ] Logout y timeout operativos.
- [ ] Regeneración de sesión verificada.
- [ ] Brute-force básico activo.
- [ ] CSRF en login/logout.
- [ ] Endpoints AJAX documentados responden contrato JSON.
- [ ] Auditoría de eventos auth.

## 16. Dependencias

- SPEC 01, 04, 05, 06.
- Tabla `users` con campos de lockout.
- Middleware Auth y CSRF.

## 17. SKILL requerida

- `skills/authentication/SKILL.md`
- `skills/security/SKILL.md`
- `skills/ajax/SKILL.md`

## 18. Estado de implementación

**PENDING**
