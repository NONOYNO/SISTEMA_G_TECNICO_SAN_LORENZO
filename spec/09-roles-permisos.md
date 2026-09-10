# SPEC 09 — Roles y Permisos (RBAC)

**Proyecto:** Unidad Educativa Fiscomisional San Lorenzo (SISTEMA_G_TECNICO_SAN_LORENZO)  
**Metodología:** POLKDEV — SPEC → SKILL → CODE  
**Estado de implementación:** PENDING

---

## 1. Objetivo

Definir el control de acceso basado en roles (RBAC) con permisos granulares para proteger módulos y acciones del SISTEMA_G_TECNICO_SAN_LORENZO.

## 2. Alcance

### Incluye
- Roles base: ADMIN, RECTOR, VICERRECTOR, DOCENTE.
- Catálogo de permisos granulares.
- Asignación role↔permission y user↔role.
- Middleware de autorización.
- UI de gestión de roles/permisos para ADMIN.

### No incluye
- ABAC por atributos contextuales complejos (grado, paralelo) en esta fase.
- Permisos directos user↔permission (solo vía roles).

## 3. Actores

| Rol | Nivel |
|-----|-------|
| ADMIN | Superusuario funcional del portal |
| RECTOR | Lectura amplia + avisos/notificaciones institucionales |
| VICERRECTOR | Gestión operativa de avisos/notif y apoyo usuarios docentes |
| DOCENTE | Consumo de información y perfil propio |

## 4. Requisitos

### 4.1 Catálogo de permisos (mínimo)

| Permiso | Descripción |
|---------|-------------|
| `users.view` | Ver listado/detalle usuarios |
| `users.create` | Crear usuarios |
| `users.update` | Editar usuarios |
| `users.delete` | Desactivar/eliminar usuarios |
| `users.assign_roles` | Asignar roles |
| `users.reset_password` | Reset password |
| `roles.view` | Ver roles |
| `roles.create` | Crear roles |
| `roles.update` | Editar roles y permisos del rol |
| `roles.delete` | Eliminar roles no sistema |
| `announcements.view` | Ver avisos |
| `announcements.create` | Crear avisos |
| `announcements.update` | Editar avisos |
| `announcements.publish` | Publicar/archivar |
| `announcements.delete` | Eliminar avisos |
| `notifications.view` | Ver notificaciones propias/admin |
| `notifications.manage` | Gestionar notificaciones globales |
| `notifications.mark_read` | Marcar leídas |
| `files.upload` | Subir archivos |
| `files.download` | Descargar |
| `files.delete` | Eliminar adjuntos |
| `reports.view` | Ver reportes |
| `audit.view` | Ver auditoría |

> Notación `users.*` en documentación = familia de permisos users.view/create/...

### 4.2 Matriz sugerida rol × familia

| Familia | ADMIN | RECTOR | VICERRECTOR | DOCENTE |
|---------|-------|--------|-------------|---------|
| users.* | all | view | view (+ update limitado opcional) | — |
| roles.* | all | view | — | — |
| announcements.* | all | view,create,update,publish | view,create,update,publish | view (publicados) |
| notifications.* | all | view,mark_read | view,mark_read,manage parcial | view,mark_read propias |
| files.* | all | upload,download | upload,download | download |
| reports.view | yes | yes | yes | — |
| audit.view | yes | yes | — | — |

## 5. Flujo funcional

1. Seeder crea roles + permissions + role_permissions.
2. Al login se cargan permissions del usuario (JOIN roles).
3. Ruta declara `middleware:permission:announcements.publish`.
4. Helper `can('announcements.publish')` en vistas para botones.
5. ADMIN edita matriz en UI Roles → guarda `role_permissions` → audit.

## 6. Reglas de negocio

1. Deny by default: sin permiso ⇒ 403.
2. Roles con `is_system=1` no se eliminan.
3. Nombre de permiso único formato `recurso.accion`.
4. Un usuario puede tener múltiples roles; permisos = unión.
5. Cambios de permisos de rol afectan en siguiente request (reload session permissions recomendado).
6. No quitar rol ADMIN al último administrador activo.

## 7. Estructura de datos

Tablas: `roles`, `permissions`, `role_permissions`, `user_roles` (SPEC 04).

**Sync permisos de rol:**
```json
{
  "role_id": 2,
  "permission_ids": [10, 11, 12, 13],
  "_csrf": "..."
}
```

## 8. Validaciones

- `roles.name`: required, unique, uppercase snake o ALPHA.
- `permissions.name`: regex `^[a-z]+\.[a-z_]+$`.
- Al borrar rol: no system; sin usuarios o reasignar antes.
- permission_ids deben existir.

## 9. Seguridad

- Solo ADMIN (o quien tenga `roles.update`) modifica matriz.
- Middleware en servidor obligatorio (no confiar en ocultar botones).
- Auditoría: `roles.update_permissions`, `users.assign_roles`.
- Evitar privilege escalation: no permitir auto-asignarse ADMIN sin ya ser ADMIN.

## 10. Interfaz

- Página Roles: tabla + detalle con checkboxes agrupados por `group_name`.
- Badges de roles en usuarios.
- En 403: mensaje “No tiene permisos para esta acción”.

## 11. AJAX requerido

| Endpoint | Método | Uso |
|----------|--------|-----|
| `/ajax/roles` | GET | Listar roles |
| `/ajax/roles/{id}/permissions` | GET | Permisos del rol |
| `/ajax/roles/{id}/permissions` | POST | Sync permisos |
| `/ajax/permissions` | GET | Catálogo |

## 12. Respuestas esperadas

**GET permissions de rol 200:**
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "role": "RECTOR",
    "permissions": ["announcements.view", "announcements.publish", "audit.view"]
  },
  "errors": {}
}
```

## 13. Manejo de errores

| Caso | HTTP |
|------|------|
| Sin roles.update | 403 |
| Rol system delete | 400 |
| Permiso inexistente | 400 |
| Escalada bloqueada | 403 |

## 14. Casos de prueba

| ID | Caso | Esperado |
|----|------|----------|
| RBAC-01 | DOCENTE users.view | 403 |
| RBAC-02 | ADMIN assign roles | 200 |
| RBAC-03 | RECTOR audit.view | Permitido según matriz |
| RBAC-04 | Usuario multi-rol | Unión de permisos |
| RBAC-05 | Delete rol ADMIN | Bloqueado |
| RBAC-06 | can() oculta botón | UI coherente + server enforce |
| RBAC-07 | Publish sin permiso | 403 |

## 15. Criterios de aceptación

- [ ] Roles base sembrados.
- [ ] Catálogo de permisos granulares implementado.
- [ ] Middleware permission funcional.
- [ ] Matriz editable por ADMIN.
- [ ] Helper `can` en vistas.
- [ ] Casos RBAC P0 pasan.
- [ ] Deny by default verificado.

## 16. Dependencias

- SPEC 04, 05, 08.
- Auth session con permissions cargados (SPEC 07).

## 17. SKILL requerida

- `skills/authorization/SKILL.md`
- `skills/roles-permissions/SKILL.md`
- `skills/security/SKILL.md`

## 18. Estado de implementación

**PENDING**
