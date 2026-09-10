---
name: authentication
description: Implementa login, registro, logout, sesiones seguras y hashing de contraseñas en SISTEMA_G_TECNICO_SAN_LORENZO. Usar al construir o auditar autenticación, timeout de sesión, CSRF de login o protección anti fuerza bruta.
---

# Autenticación — SISTEMA_G_TECNICO_SAN_LORENZO

## Nombre

`authentication`

## Propósito

Implementar autenticación segura de usuarios del portal (ADMIN, RECTOR, VICERRECTOR, DOCENTE): login, registro, logout, sesiones PHP, regeneración de ID, timeout, hash de contraseñas y protección básica contra fuerza bruta.

## Cuándo usarla

- Al implementar o modificar login/registro/logout.
- Al configurar cookies/sesión PHP.
- Al resetear o cambiar contraseñas.
- Al auditar fallos de identificación (OWASP A07).
- Antes de exponer cualquier ruta autenticada.

## SPEC relacionada

- `spec/07-autenticacion.md`
- `spec/05-seguridad.md`
- `spec/08-usuarios.md`
- `spec/21-owasp-top10.md`

## Pasos de implementación

1. Leer `spec/07-autenticacion.md` (campos, flujos, reglas).
2. Crear tablas/migraciones de `users` si aún no existen (skills `mysql`, `migrations`).
3. Implementar `AuthController` + `AuthService`.
4. **Login**: validar usuario/correo + contraseña; `password_verify`; regenerar sesión; registrar audit.
5. **Registro**: validar campos; hash con `password_hash(PASSWORD_DEFAULT)`; estado inicial **`active`**; asignar solo rol **DOCENTE** vía `RoleRepository::findByName('DOCENTE')` + `syncRoles` (nunca ADMIN desde el form); auditar; **auto-login** y redirect `/dashboard`.
6. **Logout**: destruir sesión, invalidar cookie, audit.
7. Middleware `Auth` / `Guest` en rutas.
8. Timeout de inactividad y mensaje de sesión expirada.
9. Rate limiting básico por IP/usuario (intentos fallidos).
10. CSRF en formularios de auth.
11. Probar con seeders (`credenciales.md` solo desarrollo).

## Convenciones de código

- Campos login: usuario o correo + contraseña.
- Registro mínimo: nombres, apellidos, correo, usuario, contraseña, confirmación, estado.
- Hash: `password_hash($plain, PASSWORD_DEFAULT)`; nunca texto plano.
- Sesión: guardar `user_id`, `role`, `login_at` (mínimo necesario); no guardar password.
- Tras login exitoso: `session_regenerate_id(true)`.
- Mensajes genéricos ante fallo (“Credenciales inválidas”) para no enumerar usuarios en exceso (salvo política institucional distinta en SPEC).
- Rutas sugeridas: `/login`, `/register`, `/logout`.
- Vista Bootstrap institucional (celeste/azul/blanco).

## Checklist de seguridad

- [ ] `password_hash` / `password_verify`.
- [ ] CSRF en login/registro.
- [ ] Regeneración de sesión post-login.
- [ ] Cookie `HttpOnly`, `SameSite=Lax|Strict`, `Secure` si HTTPS.
- [ ] Timeout de sesión configurado.
- [ ] Límite de intentos / bloqueo temporal.
- [ ] Audit: login OK, login fallido, logout.
- [ ] Usuarios inactivos no pueden autenticarse.
- [ ] No filtrar si el correo existe en mensajes públicos (según SPEC).

## Checklist de pruebas

- [ ] Login correcto con cada rol seeder.
- [ ] Login con password incorrecta falla y audita.
- [ ] Usuario inactivo no entra.
- [ ] Registro crea hash válido, status `active`, rol DOCENTE (no ADMIN).
- [ ] Tras registro exitoso hay sesión y redirect a dashboard.
- [ ] Confirmación de contraseña valida mismatch.
- [ ] Logout limpia sesión; rutas protegidas redirigen a login.
- [ ] Sesión regenerada (ID distinto post-login).
- [ ] CSRF inválido rechazado.
- [ ] Tras N fallos, se aplica protección anti fuerza bruta.

## Errores comunes a evitar

- Guardar contraseñas en MD5/SHA1 o texto plano.
- Confiar solo en validación frontend.
- Olvidar regenerar sesión (fijación de sesión).
- Dejar sesiones sin timeout.
- Asignar roles/permisos peligrosos desde registro público.
- Mostrar stack traces en fallos de auth.
- Usar credenciales de `credenciales.md` en producción.

## Criterio de done

Autenticación está **done** cuando login/registro/logout cumplen la SPEC, las contraseñas van hasheadas, la sesión es segura (regeneración + timeout + CSRF), hay control anti fuerza bruta básico, auditoría registra eventos clave, y las pruebas con usuarios seeder pasan.
