# SPEC 08 — Usuarios

**Proyecto:** Unidad Educativa Fiscomisional San Lorenzo (SISTEMA_G_TECNICO_SAN_LORENZO)  
**Metodología:** POLKDEV — SPEC → SKILL → CODE  
**Estado de implementación:** PENDING

---

## 1. Objetivo

Especificar el CRUD de usuarios institucionales: búsqueda, filtros, activación/desactivación, asignación de roles y reset de password por administradores autorizados.

## 2. Alcance

### Incluye
- Listado paginado con búsqueda y filtros.
- Crear, ver, editar usuarios.
- Activar / desactivar.
- Asignar uno o más roles.
- Reset password (generar temporal o definir nueva).
- Validaciones de unicidad.

### No incluye
- Importación masiva CSV (extensión futura).
- Perfil público docente avanzado (foto/portafolio) más allá de campos básicos.

## 3. Actores

| Actor | Permisos típicos |
|-------|------------------|
| ADMIN | users.* completo |
| RECTOR / VICERRECTOR | users.view; edición limitada según matriz SPEC 09 |
| DOCENTE | solo perfil propio (SPEC perfil); no CRUD usuarios |

## 4. Requisitos

1. CRUD bajo permisos `users.view`, `users.create`, `users.update`, `users.delete` (desactivar), `users.assign_roles`, `users.reset_password`.
2. Búsqueda por `first_name`, `last_name`, `email`, `username`.
3. Filtros: `status`, `role_id`, rango de fechas `created_at`.
4. Activar/desactivar sin borrar histórico.
5. Asignar roles vía `user_roles`.
6. Reset password hashea nuevo valor; opcional forzar cambio en próximo login (flag futuro).
7. No permitir desactivar el propio usuario si es el único ADMIN activo.
8. AJAX para toggle status, filtros y assign roles.

## 5. Flujo funcional

### Listar
```text
GET /users?q=&status=&role_id=&page=
→ UserService::paginate
→ View tabla o JSON
```

### Crear
```text
GET /users/create → form
POST /users + CSRF → validar → hash password → insert → assign roles → audit → redirect show
```

### Editar
```text
GET /users/{id}/edit
POST /users/{id} → update campos permitidos → sync roles si permiso → audit
```

### Toggle status
```text
POST /ajax/users/{id}/toggle-status → active↔inactive → 200 JSON
```

### Reset password
```text
POST /ajax/users/{id}/reset-password { new_password, _csrf }
→ hash → update → audit action users.reset_password
```

## 6. Reglas de negocio

1. Email y username únicos.
2. Password mínimo 8 caracteres al crear/reset.
3. Al desactivar: sesiones del usuario deben invalidarse (borrar sessions o flag checked en middleware).
4. DOCENTE no lista otros usuarios.
5. Campos editables por self-service limitados (nombre, teléfono, password propio) vs admin.
6. Roles sistema asignables solo con `users.assign_roles`.

## 7. Estructura de datos

**Payload create/update:**
```json
{
  "username": "mgomez",
  "email": "mgomez@uel.edu.ec",
  "first_name": "María",
  "last_name": "Gómez",
  "phone": "0999999999",
  "status": "active",
  "password": "********",
  "role_ids": [4],
  "_csrf": "..."
}
```

Tablas: `users`, `user_roles`, `roles`, `audit_logs`.

## 8. Validaciones

| Campo | Crear | Editar |
|-------|-------|--------|
| username | required, alpha_dash, max 50, unique | unique except self |
| email | required, email, max 150, unique | unique except self |
| first_name / last_name | required, max 100 | idem |
| phone | optional, max 30 | idem |
| password | required create; optional edit | min 8 si presente |
| status | enum | enum |
| role_ids | array de ids existentes | idem |

## 9. Seguridad

- Permiso middleware en todas las rutas.
- Password nunca se devuelve en JSON/listados.
- Reset solo con permiso; CSRF.
- Mass assignment controlado (whitelist campos).
- Audit de create/update/toggle/reset/assign_roles.

## 10. Interfaz

- Tabla Bootstrap: nombre, username, email, roles (badges), status, acciones.
- Filtros en toolbar; búsqueda con debounce AJAX opcional.
- Modal reset password.
- Modal/página assign roles (checkboxes).
- Botón switch o confirm para activar/desactivar.

## 11. AJAX requerido

| Endpoint | Método | Uso |
|----------|--------|-----|
| `/ajax/users` | GET | Listado filtrado JSON |
| `/ajax/users` | POST | Crear |
| `/ajax/users/{id}` | POST/PUT | Actualizar |
| `/ajax/users/{id}/toggle-status` | POST | Activar/desactivar |
| `/ajax/users/{id}/roles` | POST | Sync roles |
| `/ajax/users/{id}/reset-password` | POST | Reset |

## 12. Respuestas esperadas

**Listado 200:**
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "items": [ { "id": 2, "username": "mgomez", "status": "active", "roles": ["DOCENTE"] } ],
    "pagination": { "page": 1, "per_page": 15, "total": 40 }
  },
  "errors": {}
}
```

**Validación 400:** errors por campo.  
**Forbidden 403:** sin permiso.

## 13. Manejo de errores

| Caso | Respuesta |
|------|-----------|
| Usuario no encontrado | 404 |
| Email duplicado | 400/409 |
| Último admin desactivado | 400 mensaje específico |
| Password débil | 400 errors.password |

## 14. Casos de prueba

| ID | Caso | Esperado |
|----|------|----------|
| USR-01 | ADMIN crea docente | User + rol DOCENTE |
| USR-02 | Búsqueda por email | Filtra correcto |
| USR-03 | Desactivar → login | Denegado |
| USR-04 | Assign RECTOR | user_roles OK |
| USR-05 | Reset password | Login con nueva |
| USR-06 | DOCENTE GET /users | 403 |
| USR-07 | Email duplicado | Error validación |
| USR-08 | Toggle último ADMIN | Bloqueado |

## 15. Criterios de aceptación

- [ ] CRUD completo con paginación.
- [ ] Búsqueda y filtros operativos.
- [ ] Activar/desactivar con efecto en auth.
- [ ] Asignación de roles funcional.
- [ ] Reset password hasheado + auditoría.
- [ ] AJAX de toggle/roles/reset responde contrato.
- [ ] Protección último ADMIN.

## 16. Dependencias

- SPEC 04, 05, 07, 09.
- Permisos `users.*`.
- UI SPEC 06 (tabla/modales).

## 17. SKILL requerida

- `skills/users/SKILL.md`
- `skills/authorization/SKILL.md`
- `skills/ajax/SKILL.md`

## 18. Estado de implementación

**PENDING**
