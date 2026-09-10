# SPEC 02 — Requisitos Funcionales (RF)

**Proyecto:** Unidad Educativa Fiscomisional San Lorenzo (PortalInfor)  
**Metodología:** POLKDEV — SPEC → SKILL → CODE  
**Estado de implementación:** PENDING

---

## 1. Objetivo

Catalogar los requisitos funcionales del portal: autenticación, registro, usuarios, roles, permisos, avisos, notificaciones, archivos, dashboard, perfiles y auditoría, con identificadores trazables hacia SPECs y skills.

## 2. Alcance

### Incluye
- RF de módulos del núcleo informativo-administrativo.
- Prioridad sugerida (P0 crítico, P1 alto, P2 medio).
- Trazabilidad a SPECs 07–12 y módulos de perfil/dashboard.

### No incluye
- Requisitos no funcionales (ver SPEC 03).
- Detalle de esquema SQL (ver SPEC 04).

## 3. Actores

ADMIN, RECTOR, VICERRECTOR, DOCENTE, Visitante (no autenticado).

## 4. Requisitos

### 4.1 Autenticación — RF-AUTH

| ID | Requisito | Prioridad | SPEC |
|----|-----------|-----------|------|
| RF-AUTH-01 | Login con usuario **o** correo + password | P0 | 07 |
| RF-AUTH-02 | Logout invalida sesión | P0 | 07 |
| RF-AUTH-03 | Timeout de inactividad configurable | P0 | 07 |
| RF-AUTH-04 | Regeneración de session ID en login exitoso | P0 | 07 |
| RF-AUTH-05 | Bloqueo temporal tras N intentos fallidos | P1 | 07 |
| RF-AUTH-06 | Protección CSRF en formulario login y AJAX | P0 | 07 |

### 4.2 Registro — RF-REG

| ID | Requisito | Prioridad |
|----|-----------|-----------|
| RF-REG-01 | Registro de usuario con datos mínimos (nombre, email, username, password) | P1 |
| RF-REG-02 | Estado inicial `pending` o `active` según configuración institucional | P1 |
| RF-REG-03 | Asignación de rol por defecto DOCENTE (configurable) | P1 |
| RF-REG-04 | Validación de unicidad email/username | P0 |

### 4.3 Usuarios — RF-USER

| ID | Requisito | Prioridad | SPEC |
|----|-----------|-----------|------|
| RF-USER-01 | CRUD de usuarios (crear, listar, ver, editar) | P0 | 08 |
| RF-USER-02 | Búsqueda por nombre, email, username | P0 | 08 |
| RF-USER-03 | Filtros por rol, estado, fecha | P1 | 08 |
| RF-USER-04 | Activar / desactivar usuario | P0 | 08 |
| RF-USER-05 | Asignar / revocar roles | P0 | 08 |
| RF-USER-06 | Reset de password por ADMIN | P0 | 08 |
| RF-USER-07 | Impedir auto-desactivación del último ADMIN | P0 | 08 |

### 4.4 Roles — RF-ROLE

| ID | Requisito | Prioridad | SPEC |
|----|-----------|-----------|------|
| RF-ROLE-01 | Listar roles del sistema | P0 | 09 |
| RF-ROLE-02 | Crear/editar roles personalizados (además de los 4 base) | P1 | 09 |
| RF-ROLE-03 | Proteger roles sistema (ADMIN, RECTOR, VICERRECTOR, DOCENTE) de borrado | P0 | 09 |

### 4.5 Permisos — RF-PERM

| ID | Requisito | Prioridad | SPEC |
|----|-----------|-----------|------|
| RF-PERM-01 | Permisos granulares asignables a roles | P0 | 09 |
| RF-PERM-02 | Catálogo mínimo: `users.*`, `roles.*`, `announcements.*`, `notifications.*`, `files.*`, `reports.view`, `audit.view` | P0 | 09 |
| RF-PERM-03 | Middleware verifica permiso por ruta/acción | P0 | 09 |

### 4.6 Avisos — RF-ANN

| ID | Requisito | Prioridad | SPEC |
|----|-----------|-----------|------|
| RF-ANN-01 | CRUD avisos | P0 | 10 |
| RF-ANN-02 | Estados: Borrador / Publicado / Archivado | P0 | 10 |
| RF-ANN-03 | Campos: título, descripción, contenido, categoría, prioridad, fechas, destinatarios | P0 | 10 |
| RF-ANN-04 | Publicación genera notificaciones a destinatarios | P0 | 10/11 |
| RF-ANN-05 | Adjuntos opcionales en aviso | P1 | 10/12 |

### 4.7 Notificaciones — RF-NOTIF

| ID | Requisito | Prioridad | SPEC |
|----|-----------|-----------|------|
| RF-NOTIF-01 | Listado en cards | P0 | 11 |
| RF-NOTIF-02 | Marcar como leída (una / todas) | P0 | 11 |
| RF-NOTIF-03 | Prioridad visual (alta/media/baja) | P1 | 11 |
| RF-NOTIF-04 | Contador de no leídas en navbar | P0 | 11 |
| RF-NOTIF-05 | Soporte de adjuntos vinculados | P1 | 11/12 |

### 4.8 Archivos — RF-FILE

| ID | Requisito | Prioridad | SPEC |
|----|-----------|-----------|------|
| RF-FILE-01 | Upload con whitelist de extensiones | P0 | 12 |
| RF-FILE-02 | Rechazo de ejecutables (php, exe, bat, etc.) | P0 | 12 |
| RF-FILE-03 | Validación MIME real (fileinfo) | P0 | 12 |
| RF-FILE-04 | Rename seguro + storage fuera de public | P0 | 12 |
| RF-FILE-05 | Descarga autenticada anti path traversal | P0 | 12 |

### 4.9 Dashboard — RF-DASH

| ID | Requisito | Prioridad |
|----|-----------|-----------|
| RF-DASH-01 | Dashboard por rol con KPIs relevantes | P0 |
| RF-DASH-02 | Resumen de avisos recientes y notificaciones no leídas | P0 |
| RF-DASH-03 | Accesos rápidos según permisos | P1 |

### 4.10 Perfiles — RF-PROF

| ID | Requisito | Prioridad |
|----|-----------|-----------|
| RF-PROF-01 | Ver/editar perfil propio (datos básicos) | P0 |
| RF-PROF-02 | Cambio de password propio con password actual | P0 |
| RF-PROF-03 | Vistas/widgets diferenciados por rol (admin, rector, vicerrector, docente) | P1 |

### 4.11 Auditoría — RF-AUDIT

| ID | Requisito | Prioridad |
|----|-----------|-----------|
| RF-AUDIT-01 | Registrar login/logout, CRUD usuarios, cambios roles, publicación avisos, uploads | P0 |
| RF-AUDIT-02 | Consulta de logs para roles con `audit.view` | P1 |
| RF-AUDIT-03 | Campos: user_id, action, entity, entity_id, ip, user_agent, payload, created_at | P0 |

## 5. Flujo funcional

1. Visitante se autentica o registra → sesión.
2. Sistema carga permisos del usuario (roles ∪ permissions).
3. Dashboard muestra módulos permitidos.
4. Flujos de negocio por módulo según SPECs 07–12.
5. Acciones sensibles escriben `audit_logs`.

## 6. Reglas de negocio

1. Sin permiso explícito ⇒ denegar (deny by default).
2. DOCENTE no administra usuarios ni roles.
3. Aviso publicado no editable destructivamente sin traza (preferir nueva versión o audit).
4. Usuario desactivado pierde acceso inmediato en siguiente request (middleware).
5. Registro puede deshabilitarse por config `REGISTRATION_ENABLED=false`.

## 7. Estructura de datos

Referencia a tablas de SPEC 04. Mapeo RF → tablas:

| RF grupo | Tablas |
|----------|--------|
| AUTH/REG | users, sessions |
| USER/ROLE/PERM | users, roles, permissions, user_roles, role_permissions |
| ANN | announcements, attachments |
| NOTIF | notifications, notification_reads, attachments |
| FILE | attachments |
| AUDIT | audit_logs |

## 8. Validaciones

Cada RF mutador debe validar:
- Autenticación y permiso.
- Entrada (tipos, longitudes, enums).
- Integridad referencial (FK existen).
- CSRF en operaciones de escritura.

## 9. Seguridad

- Cumplir SPEC 05 en todos los RF.
- No exponer IDs internos innecesarios en URLs públicas sin auth.
- Auditoría no editable por UI (solo lectura).

## 10. Interfaz

- Menú lateral filtrado por permisos.
- Cards y tablas Bootstrap según módulo.
- Mensajes flash y toasts para feedback RF.

## 11. AJAX requerido

Mínimo por grupo:
- AUTH: login AJAX opcional + check sesión.
- USER: list/filter/toggle status.
- ANN: publish/archive.
- NOTIF: mark read / mark all.
- FILE: upload progress + delete metadata.

## 12. Respuestas esperadas

Contrato JSON unificado (SPEC 00/01). Mensajes en español orientados al usuario institucional.

## 13. Manejo de errores

| Situación | Respuesta |
|-----------|-----------|
| Sin auth | 401 |
| Sin permiso | 403 |
| Validación | 400 + errors |
| Recurso ausente | 404 |
| Conflicto (email duplicado) | 409 o 400 con mensaje |

## 14. Casos de prueba

| ID | RF | Caso | Esperado |
|----|----|------|----------|
| RF-T01 | AUTH-01 | Login email válido | Dashboard |
| RF-T02 | USER-04 | Desactivar docente | No puede login |
| RF-T03 | PERM-03 | Docente abre /users | 403 |
| RF-T04 | ANN-04 | Publicar aviso | Notificaciones creadas |
| RF-T05 | NOTIF-02 | Marcar leída | Contador decrementa |
| RF-T06 | FILE-02 | Subir .exe | Rechazo |
| RF-T07 | AUDIT-01 | Reset password | Log creado |

## 15. Criterios de aceptación

- [ ] Todos los RF P0 tienen SPEC o sección detallada.
- [ ] Matriz rol × módulo documentada en SPEC 09.
- [ ] Trazabilidad RF → tablas → endpoints.
- [ ] Casos de prueba P0 ejecutables en fase QA.

## 16. Dependencias

- SPECs 00, 01, 04, 05, 07–12.
- Skills de cada módulo.

## 17. SKILL requerida

- `skills/authentication/SKILL.md`
- `skills/authorization/SKILL.md`
- `skills/users/SKILL.md`
- `skills/roles-permissions/SKILL.md`
- `skills/announcements/SKILL.md`
- `skills/notifications/SKILL.md`
- `skills/file-upload/SKILL.md`
- `skills/testing/SKILL.md`

## 18. Estado de implementación

**PENDING**
