---
name: roles-permissions
description: Implementa RBAC de SISTEMA_G_TECNICO_SAN_LORENZO (roles ADMIN/RECTOR/VICERRECTOR/DOCENTE, permisos granulares, pivotes). Usar al crear tablas de permisos, seeders de roles, pantallas de asignación o cambios de política de acceso.
---

# Roles y Permisos — SISTEMA_G_TECNICO_SAN_LORENZO

## Nombre

`roles-permissions`

## Propósito

Construir y mantener el sistema RBAC (Role Based Access Control) del portal: roles institucionales, permisos granulares, tablas pivote y administración segura de asignaciones, alineado a la separación usuarios / roles / permisos.

## Cuándo usarla

- Al crear el esquema de roles y permisos.
- Al seedear roles y catálogo de permisos.
- Al implementar pantallas de gestión de roles/permisos (ADMIN).
- Al añadir un permiso nuevo para un módulo.
- Junto con la skill `authorization` al proteger features.

## SPEC relacionada

- `spec/09-roles-permisos.md`
- `spec/08-usuarios.md`
- `spec/05-seguridad.md`
- `spec/19-seeders.md`

## Pasos de implementación

1. Leer `spec/09-roles-permisos.md`.
2. Migraciones sugeridas:
   - `roles` (`id`, `name`, `slug`, `description`)
   - `permissions` (`id`, `name`, `slug`, `module`)
   - `role_permissions` (`role_id`, `permission_id`)
   - `user_roles` (`user_id`, `role_id`) — o `role_id` en `users` si SPEC define 1:1
3. Seedear roles: `ADMIN`, `RECTOR`, `VICERRECTOR`, `DOCENTE`.
4. Seedear permisos granulares (users.*, roles.*, announcements.*, notifications.*, files.*, reports.*, audit.*).
5. Mapear role_permissions según perfiles SPEC (14–17).
6. Implementar UI ADMIN para ver/asignar (si está en alcance).
7. Cache opcional de permisos en sesión (invalidar al cambiar roles).
8. Integrar con `AuthorizationService::can()`.
9. Auditar cambios de matriz de permisos.

## Convenciones de código

- Slug de rol: `admin`, `rector`, `vicerrector`, `docente` (o UPPER según SPEC; ser consistente).
- Slug de permiso: `recurso.accion` (`announcements.publish`).
- Un usuario tiene al menos un rol; por defecto el modelo institucional es un rol principal.
- No enviar lista editable de permisos al cliente como verdad.
- Nombres visibles en español en UI; slugs estables en código.
- Evitar permisos “god” genéricos salvo `*` documentado solo para ADMIN vía seeder.

### Catálogo mínimo de permisos

```text
users.view | users.create | users.edit | users.delete
roles.view | roles.create | roles.edit | roles.delete
announcements.view | announcements.create | announcements.edit
announcements.delete | announcements.publish
notifications.view | notifications.create
files.upload | files.download | files.delete
reports.view | audit.view
```

## Checklist de seguridad

- [ ] Solo ADMIN (o permiso explícito) modifica roles/permisos.
- [ ] CSRF en formularios de asignación.
- [ ] Impedir auto-otorgarse permisos.
- [ ] Validar IDs de rol/permiso existentes.
- [ ] Cambios auditados.
- [ ] Sesión/permisos en caché invalidados tras cambio.
- [ ] Mínimo privilegio en roles no admin.

## Checklist de pruebas

- [ ] Seeders crean 4 roles y permisos esperados.
- [ ] Matriz role_permissions coincide con SPEC de perfiles.
- [ ] Asignar/quitar permiso cambia acceso real.
- [ ] Usuario con dos cambios de rol refleja nuevo acceso tras re-login o refresh de sesión.
- [ ] Intento de editar roles sin permiso → 403.
- [ ] No se pueden borrar roles del sistema si SPEC los marca protegidos.

## Errores comunes a evitar

- Hardcodear checks solo por nombre de rol en 20 archivos distintos.
- Permisos en frontend sin backend.
- Duplicar slugs o permisos “parecidos” (`edit` vs `update`) sin estándar.
- Dar a DOCENTE permisos de publicación por error en seeder.
- Eliminar pivotes sin migrar usuarios afectados.
- Confundir `role_permissions` con `user_roles`.

## Criterio de done

RBAC está **done** cuando existen tablas/pivotes, los 4 roles y permisos granulares están seedeados según SPEC, la administración (si aplica) es segura, `can()` refleja la matriz, y las pruebas demuestran acceso diferencial real por rol.
