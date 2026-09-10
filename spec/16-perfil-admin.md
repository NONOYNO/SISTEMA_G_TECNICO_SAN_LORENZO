# SPEC 16 — Perfil ADMIN

**Proyecto:** Unidad Educativa Fiscomisional San Lorenzo  
**Stack:** PHP 8 + MySQL + PDO + MVC + Bootstrap 5 + AJAX + XAMPP  
**Módulo:** Administración total de la plataforma  
**Estado:** PENDING

---

## 1. Objetivo

Definir el perfil y panel del rol ADMIN con gestión total: usuarios, roles, permisos, avisos, notificaciones, archivos, configuración institucional, auditoría y reportes, garantizando controles de seguridad y trazabilidad.

## 2. Alcance

**Incluye:**
- Dashboard administrativo.
- CRUD usuarios (crear, editar, activar/desactivar, reset password seguro).
- Gestión roles y permisos (RBAC).
- Supervisión CRUD avisos/notificaciones/archivos.
- Configuración del sistema (nombre institución, límites upload, políticas).
- Consulta de `audit_logs` con filtros.
- Reportes autorizados (usuarios, avisos, actividad).
- Perfil propio del admin.

**Excluye:**
- Cambios de infraestructura XAMPP (ver `spec/24-despliegue-xampp.md`).
- Ejecución de backups (orquestación en `spec/23-backup.md`; ADMIN puede disparar si se implementa UI).

## 3. Actores

| Actor | Capacidad |
|-------|-----------|
| ADMIN | Acceso completo sujeto a permisos `*.*` / rol ADMIN |
| Otros roles | Sin acceso a rutas `/admin/*` |

## 4. Requisitos

### Funcionales
- RF-PA01: Listar/buscar/filtrar usuarios (rol, estado, texto).
- RF-PA02: Crear usuario y asignar rol(es).
- RF-PA03: Editar usuario; activar/desactivar.
- RF-PA04: Resetear contraseña con token/flujo seguro o generación temporal auditada.
- RF-PA05: CRUD roles; asignar permisos a roles.
- RF-PA06: Ver catálogo de permisos; crear permisos solo si política lo permite.
- RF-PA07: Gestionar avisos y notificaciones de cualquier autor.
- RF-PA08: Gestionar/eliminar archivos con auditoría.
- RF-PA09: Editar configuración (`settings` key-value).
- RF-PA10: Consultar auditoría con filtros (usuario, acción, entidad, fechas, IP).
- RF-PA11: Generar reportes básicos (CSV/HTML) según `reports.view`.
- RF-PA12: Impedir auto-eliminación del último ADMIN activo.

### No funcionales
- RNF-PA01: Tablas DataTables-like o paginación AJAX.
- RNF-PA02: Todas las mutaciones con CSRF.
- RNF-PA03: Logs de seguridad en acciones críticas.

## 5. Flujo funcional

### Alta de usuario
```text
Admin → Usuarios → Nuevo
  → Validar datos → password_hash
  → Asignar rol → audit USER_CREATED
  → (opcional) email de bienvenida si se habilita
```

### Cambio de permisos
```text
Roles → seleccionar rol → marcar permisos → guardar transacción
  → audit ROLE_PERMISSIONS_UPDATED
  → invalidar caché de permisos en sesión (si existe)
```

### Consulta auditoría
```text
Auditoría → filtros → AJAX list → export opcional
```

## 6. Reglas de negocio

- RN-PA01: Solo ADMIN (o permiso equivalente) accede a `/admin`.
- RN-PA02: No eliminar físicamente usuarios con historial; preferir `status=inactive`.
- RN-PA03: Debe existir al menos un ADMIN activo.
- RN-PA04: ADMIN no puede quitarse a sí mismo el rol ADMIN si es el único.
- RN-PA05: Permisos granulares; UI no es fuente de verdad.
- RN-PA06: Configuración sensible (DB, secrets) no editable desde UI; solo `.env`.
- RN-PA07: Reportes no incluyen hashes de contraseña ni tokens.
- RN-PA08: Acciones destructivas requieren confirmación modal + CSRF.

## 7. Estructura de datos

### Tablas
`users`, `roles`, `permissions`, `user_roles`, `role_permissions`, `announcements`, `notifications`, `attachments`, `audit_logs`, `settings` (sugerida), `sessions`.

### `settings` (sugerida)
| Campo | Tipo |
|-------|------|
| key | VARCHAR PK |
| value | TEXT |
| updated_by | FK |
| updated_at | DATETIME |

### Permisos mínimos ADMIN
```text
users.*, roles.*, announcements.*, notifications.*,
files.*, reports.view, audit.view, settings.manage
```

## 8. Validaciones

- Email único, username único.
- Política de contraseña institucional.
- IDs existentes en asignación rol/permiso.
- Filtros de auditoría: fechas válidas, action whitelist.
- Export: límite de filas para evitar DoS.
- Upload de assets de configuración (logo): whitelist imagen.

## 9. Seguridad

- Middleware `AdminMiddleware` + chequeo de permisos.
- Regeneración de sesión en login; timeout.
- Rate limiting en login y reset password.
- PDO prepared statements; XSS escape.
- No exponer listados de usuarios a roles no autorizados vía IDOR.
- Separar logs `security.log` para denegaciones.
- Principio de mínimo privilegio al crear nuevos roles.

## 10. Interfaz

- Layout admin: sidebar denso con secciones Usuarios, Roles, Permisos, Avisos, Notificaciones, Archivos, Configuración, Auditoría, Reportes.
- Cards KPI en dashboard.
- Tablas responsive, badges de estado.
- Modales para create/edit.
- Paleta celeste/azul/blanco institucional.
- Breadcrumbs en todas las secciones.

## 11. AJAX requerido

| Endpoint | Método | Uso |
|----------|--------|-----|
| `/ajax/admin/users` | GET/POST | Listar/crear |
| `/ajax/admin/users/{id}` | PUT/PATCH | Editar/estado |
| `/ajax/admin/roles` | GET/POST/PUT | Roles |
| `/ajax/admin/roles/{id}/permissions` | PUT | Asignar permisos |
| `/ajax/admin/announcements` | * | Supervisión |
| `/ajax/admin/settings` | GET/PUT | Config |
| `/ajax/admin/audit` | GET | Filtros auditoría |
| `/ajax/admin/reports/{type}` | GET | Reportes |

## 12. Respuestas esperadas

**Usuario creado:**
```json
{
  "success": true,
  "message": "Usuario creado",
  "data": { "id": 25, "email": "nuevo@uesanlorenzo.edu" }
}
```

**Intento eliminar último ADMIN:**
```json
{
  "success": false,
  "message": "Debe existir al menos un administrador activo",
  "code": "LAST_ADMIN"
}
```

## 13. Manejo de errores

- Conflictos unique → 409/422 con campo.
- Fallo transacción permisos → rollback completo.
- Error reporte → mensaje genérico + log.
- 403 en rutas admin para no-ADMIN (sin filtrar existencia de IDs sensibles).

## 14. Casos de prueba

| ID | Caso | Esperado |
|----|------|----------|
| ADM-001 | CRUD usuario | OK + audit |
| ADM-002 | Asignar rol DOCENTE | OK |
| ADM-003 | Modificar permisos rol | Efecto inmediato en authz |
| ADM-004 | Desactivar usuario | No puede login |
| ADM-005 | Eliminar último ADMIN | Bloqueado |
| ADM-006 | RECTOR accede `/admin/users` | 403 |
| ADM-007 | Filtro auditoría por IP | Resultados correctos |
| ADM-008 | Cambiar setting max_upload | Persistido |
| ADM-009 | Export reporte usuarios | Sin hashes |
| ADM-010 | CSRF inválido en create user | 419/403 |

## 15. Criterios de aceptación

- [ ] ADMIN administra usuarios, roles y permisos de punta a punta.
- [ ] Puede supervisar avisos, notificaciones y archivos.
- [ ] Configuración no sensible editable; secretos fuera de UI.
- [ ] Auditoría consultable y filtrable.
- [ ] Reportes básicos disponibles.
- [ ] Protecciones LAST_ADMIN y CSRF activas.

## 16. Dependencias

- Specs 07–12, 13, 22, 20, 21
- Skills: `users`, `roles-permissions`, `authorization`, `security`, `ajax`, `bootstrap-ui`

## 17. SKILL requerida

```text
skills/profile-admin/SKILL.md
```

## 18. Estado de implementación

**PENDING**
