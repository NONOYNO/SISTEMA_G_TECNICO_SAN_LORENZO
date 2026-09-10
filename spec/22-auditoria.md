# SPEC 22 — Auditoría (`audit_logs`)

**Proyecto:** Unidad Educativa Fiscomisional San Lorenzo  
**Stack:** PHP 8 + MySQL + PDO + MVC + Bootstrap 5 + AJAX + XAMPP  
**Módulo:** Auditoría de eventos del sistema  
**Estado:** PENDING

---

## 1. Objetivo

Implementar un registro de auditoría confiable en la tabla `audit_logs` para trazabilidad de autenticación, operaciones CRUD, cambios de permisos y descargas de archivos, consultable por ADMIN.

## 2. Alcance

**Incluye:**
- Modelo/servicio `AuditLogger`.
- Tabla `audit_logs` con campos requeridos.
- Instrumentación de eventos obligatorios.
- UI ADMIN de consulta/filtros/export limitado.
- Retención básica y protección contra manipulación por no-ADMIN.

**Excluye:**
- SIEM externo.
- Alteración/borrado masivo por UI (solo mantenimiento técnico documentado).

## 3. Actores

| Actor | Capacidad |
|-------|-----------|
| Sistema | Escribe logs automáticamente |
| ADMIN | Consulta con `audit.view` |
| Otros roles | Sin acceso (salvo permiso explícito futuro) |

## 4. Requisitos

### Funcionales
- RF-A01: Persistir eventos con: `user_id`, `action`, `entity`, `entity_id`, `ip`, `user_agent`, `created_at`.
- RF-A02: Campos adicionales recomendados: `result` (success/fail), `metadata` JSON, `url`.
- RF-A03: Registrar login success/fail, logout.
- RF-A04: Registrar CRUD de users, roles, permissions, announcements, notifications, attachments.
- RF-A05: Registrar downloads y deletes de archivos.
- RF-A06: Registrar publish/archive de avisos y cambios de permisos.
- RF-A07: Listado ADMIN con filtros (usuario, action, entity, rango fechas, IP).
- RF-A08: Paginación AJAX.
- RF-A09: `user_id` nullable para eventos anónimos (login fallido con email desconocido).

### No funcionales
- RNF-A01: Escritura best-effort: fallo de audit no rompe negocio, pero se loguea en `security.log`.
- RNF-A02: Inserciones parametrizadas PDO.
- RNF-A03: Índice por `created_at`, `action`, `user_id`.

## 5. Flujo funcional

```text
Acción de usuario/sistema
  → Service de dominio ejecuta lógica
  → AuditLogger::log([...])
  → INSERT audit_logs
  → (ADMIN) consulta /admin/audit → AJAX filtros → tabla
```

### Login fallido
```text
POST login inválido → NO crear sesión → audit action=LOGIN_FAILED user_id=null metadata={email}
```

## 6. Reglas de negocio

- RN-A01: Inmutable desde aplicación: no UPDATE de filas de auditoría en UI.
- RN-A02: DELETE solo por política de retención CLI (mantenimiento), nunca por DOCENTE/RECTOR.
- RN-A03: No guardar contraseñas, tokens CSRF, ni contenidos de archivos.
- RN-A04: `action` usa catálogo controlado (enum/string whitelist).
- RN-A05: IP desde conexión confiable; no confiar ciegamente en X-Forwarded-For salvo proxy configurado.
- RN-A06: Truncar `user_agent` a 512 chars.
- RN-A07: `entity` nombre lógico (`user`, `announcement`, `file`, `auth`…).

### Catálogo mínimo de `action`
```text
LOGIN_SUCCESS
LOGIN_FAILED
LOGOUT
USER_CREATED
USER_UPDATED
USER_DELETED
USER_STATUS_CHANGED
USER_PROFILE_UPDATED
ROLE_ASSIGNED
ROLE_PERMISSIONS_UPDATED
ANNOUNCEMENT_CREATED
ANNOUNCEMENT_UPDATED
ANNOUNCEMENT_PUBLISHED
ANNOUNCEMENT_ARCHIVED
ANNOUNCEMENT_DELETED
NOTIFICATION_CREATED
NOTIFICATION_READ
FILE_UPLOADED
FILE_DOWNLOADED
FILE_DELETED
SETTINGS_UPDATED
REPORT_VIEWED
```

## 7. Estructura de datos

### Tabla `audit_logs`
| Campo | Tipo | Obligatorio | Descripción |
|-------|------|-------------|-------------|
| id | BIGINT AI PK | sí | |
| user_id | BIGINT NULL FK users | no | Actor |
| action | VARCHAR(64) | sí | Código evento |
| entity | VARCHAR(64) | sí | Entidad afectada |
| entity_id | BIGINT NULL | no | ID entidad |
| ip | VARCHAR(45) | sí | IPv4/IPv6 |
| user_agent | VARCHAR(512) NULL | no | UA |
| result | VARCHAR(16) NULL | no | success/fail |
| metadata | JSON NULL | no | Detalle no sensible |
| created_at | DATETIME | sí | Default CURRENT_TIMESTAMP |

### Índices
```text
idx_audit_created_at (created_at)
idx_audit_user (user_id)
idx_audit_action (action)
idx_audit_entity (entity, entity_id)
```

## 8. Validaciones

- `action` ∈ whitelist.
- `entity` formato slug corto.
- Filtros ADMIN: fechas válidas; max rango (ej. 90 días) por consulta.
- Export CSV: tope de filas (ej. 5000).
- Sanitizar salida HTML al listar metadata.

## 9. Seguridad

- Solo `audit.view` lee.
- Escritura interna (servicio), no endpoint público de “crear log” abierto.
- Protección XSS en visor.
- Separar `security.log` para denegaciones repetidas.
- Cumple OWASP A09.

## 10. Interfaz

- ADMIN → Auditoría: tabla Bootstrap responsive.
- Filtros: usuario, acción, entidad, desde/hasta, IP.
- Badge de `result`.
- Detalle en modal (metadata pretty-print).
- Botón export CSV (opcional).

## 11. AJAX requerido

| Endpoint | Método | Uso |
|----------|--------|-----|
| `/ajax/admin/audit` | GET | Listado filtrado paginado |
| `/ajax/admin/audit/{id}` | GET | Detalle |
| `/ajax/admin/audit/export` | GET | CSV limitado |

## 12. Respuestas esperadas

```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1001,
        "user_id": 1,
        "action": "FILE_DOWNLOADED",
        "entity": "file",
        "entity_id": 55,
        "ip": "127.0.0.1",
        "user_agent": "Mozilla/5.0...",
        "created_at": "2026-09-08 15:00:00"
      }
    ],
    "pagination": { "page": 1, "per_page": 20, "total": 200 }
  }
}
```

## 13. Manejo de errores

- Fallo INSERT audit → log archivo + continuar request.
- Consulta sin permiso → 403.
- Filtros inválidos → 422.
- Export demasiado grande → 400 con mensaje de reducir rango.

## 14. Casos de prueba

| ID | Caso | Esperado |
|----|------|----------|
| AUD-001 | Login OK | LOGIN_SUCCESS con user_id |
| AUD-002 | Login fail | LOGIN_FAILED |
| AUD-003 | Crear usuario | USER_CREATED |
| AUD-004 | Publicar aviso | ANNOUNCEMENT_PUBLISHED |
| AUD-005 | Descargar archivo | FILE_DOWNLOADED |
| AUD-006 | DOCENTE GET audit | 403 |
| AUD-007 | Filtro por action | Solo esos eventos |
| AUD-008 | Metadata sin password | No contiene secretos |
| AUD-009 | Intento UPDATE log vía API | No existe / 405 |
| AUD-010 | Logout | LOGOUT registrado |

## 15. Criterios de aceptación

- [ ] Tabla con campos `user_id, action, entity, entity_id, ip, user_agent, created_at`.
- [ ] Eventos login, CRUD y downloads instrumentados.
- [ ] UI ADMIN filtrable vía AJAX.
- [ ] Sin manipulación por roles no autorizados.
- [ ] Sin secretos en logs.
- [ ] Índices creados en migración.

## 16. Dependencias

- `spec/18-migraciones.md`, `spec/16-perfil-admin.md`, `spec/21-owasp-top10.md`, `spec/07-autenticacion.md`, `spec/12-archivos.md`
- Skills: `security`, `mysql`, `ajax`

## 17. SKILL requerida

```text
skills/audit/SKILL.md
```

(Complementaria: `skills/security/SKILL.md`)

## 18. Estado de implementación

**PENDING**
