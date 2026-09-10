---
name: users
description: Gestiona el módulo de usuarios de SISTEMA_G_TECNICO_SAN_LORENZO (CRUD, búsqueda, filtros, activar/desactivar, asignación de roles, perfil). Usar al implementar o modificar administración de usuarios.
---

# Usuarios — SISTEMA_G_TECNICO_SAN_LORENZO

## Nombre

`users`

## Propósito

Implementar la gestión de usuarios institucionales: listado, búsqueda, filtros, creación, edición, activación/desactivación, eliminación según reglas, asignación de roles, consulta de perfil y restablecimiento seguro de contraseña.

## Cuándo usarla

- Al construir el módulo de administración de usuarios.
- Al conectar registro/login con el modelo `User`.
- Al implementar perfil de docente u otros perfiles.
- Al añadir filtros AJAX, paginación o asignación de roles.
- Al escribir pruebas del ciclo de vida del usuario.

## SPEC relacionada

- `spec/08-usuarios.md`
- `spec/07-autenticacion.md`
- `spec/09-roles-permisos.md`
- `spec/14-perfil-docente.md` (y perfiles relacionados)

## Pasos de implementación

1. Leer `spec/08-usuarios.md` (campos, reglas, AJAX, aceptación).
2. Asegurar migraciones de `users` + pivotes de roles.
3. Implementar `UserController`, `UserService`, `UserRepository`, `UserValidator`.
4. **Listado**: paginación, búsqueda (nombre, correo, usuario), filtros (rol, estado).
5. **Crear/Editar**: validación backend; hash de password si se define; no permitir auto-escalada.
6. **Activar/Desactivar**: soft flag `is_active` / estado; usuarios inactivos sin login.
7. **Eliminar**: solo según reglas (evitar borrar último ADMIN; preferir desactivar).
8. **Asignar roles**: UI + backend con permiso `users.edit` / `roles.assign`.
9. **Perfil**: vista de consulta; edición limitada de campos permitidos por rol.
10. **Reset password**: flujo seguro (solo admin autorizado o token según SPEC).
11. AJAX para búsqueda/filtros/cambio de estado si mejora UX.
12. Audit de create/update/delete/role change.

## Convenciones de código

- Campos: `first_name`, `last_name`, `email`, `username`, `password`, `status`/`is_active`, timestamps.
- Email y username únicos.
- Nunca devolver `password` en JSON/vistas.
- Permisos: `users.view`, `users.create`, `users.edit`, `users.delete`.
- Formularios Bootstrap; tablas responsive.
- Respuestas AJAX con contrato estándar del proyecto.
- Mensajes flash en español.

## Checklist de seguridad

- [ ] Permisos backend en cada acción.
- [ ] CSRF en mutaciones.
- [ ] Password hasheado; no loguear passwords.
- [ ] Mass assignment controlado (whitelist de campos).
- [ ] Un usuario no se asigna rol superior sin autorización.
- [ ] Anti-IDOR en show/edit/update.
- [ ] Sanitización/escape en listados y perfil.
- [ ] Audit de cambios sensibles.

## Checklist de pruebas

- [ ] Listar/buscar/filtrar usuarios.
- [ ] Crear usuario válido; rechazar duplicados email/username.
- [ ] Editar datos permitidos.
- [ ] Desactivar impide login; reactivar lo permite.
- [ ] Asignar rol DOCENTE/VICERRECTOR/etc. y verificar acceso.
- [ ] Eliminación/desactivación respeta reglas de negocio.
- [ ] Usuario sin `users.view` recibe 403.
- [ ] AJAX de búsqueda funciona con CSRF/auth.
- [ ] Perfil docente solo edita campos autorizados.

## Errores comunes a evitar

- Exponer hash o password en API.
- Permitir borrado duro indiscriminado.
- Editar roles desde formulario de registro público.
- Filtros SQL con concatenación.
- Confundir “estado inactivo” con “sin rol”.
- Olvidar invalidar sesión al desactivar usuario logueado (si SPEC lo pide).

## Criterio de done

El módulo de usuarios está **done** cuando el CRUD + búsqueda/filtros + estados + roles cumplen la SPEC, la seguridad (authz, CSRF, hash, anti-IDOR) está cubierta, hay auditoría, las pruebas pasan con seeders, y no se filtran datos sensibles.
