# SPEC 17 — Perfil RECTOR

**Proyecto:** Unidad Educativa Fiscomisional San Lorenzo  
**Stack:** PHP 8 + MySQL + PDO + MVC + Bootstrap 5 + AJAX + XAMPP  
**Módulo:** Consulta institucional del rector  
**Estado:** PENDING

---

## 1. Objetivo

Proveer al rol RECTOR un perfil orientado a **consulta**: información institucional, avisos, notificaciones y reportes autorizados según permisos configurables, sin capacidades de administración total ni publicación editorial (salvo que un permiso explícito se asigne).

## 2. Alcance

**Incluye:**
- Dashboard de consulta.
- Lectura de avisos publicados y notificaciones.
- Descarga de archivos autorizados.
- Consulta de información institucional (misión, contactos, comunicados vigentes).
- Reportes de solo lectura si tiene `reports.view`.
- Consulta limitada de usuarios **solo** si tiene `users.view`.
- Edición de datos personales permitidos en su perfil.

**Excluye por defecto:**
- Crear/editar/publicar avisos.
- Asignar roles/permisos.
- Configuración del sistema.
- Acceso a auditoría completa (`audit.view` opcional si se asigna).

## 3. Actores

| Actor | Capacidad |
|-------|-----------|
| RECTOR | Consulta según permisos |
| ADMIN | Configura permisos del rol RECTOR |
| VICERRECTOR | Publica lo que el rector consulta |
| DOCENTE | Sin relación directa |

## 4. Requisitos

### Funcionales
- RF-PR01: Ver dashboard con avisos vigentes y resumen autorizado.
- RF-PR02: Listar y detallar avisos publicados.
- RF-PR03: Ver notificaciones y marcarlas leídas.
- RF-PR04: Descargar adjuntos autorizados.
- RF-PR05: Ver página de información institucional.
- RF-PR06: Acceder a reportes solo si `reports.view`.
- RF-PR07: Si `users.view`, ver listado lectura (sin botones crear/eliminar).
- RF-PR08: Actualizar perfil propio (campos permitidos).
- RF-PR09: Toda acción de escritura adicional depende de permiso explícito (extensible).

### No funcionales
- RNF-PR01: UI de consulta clara, sin menús admin.
- RNF-PR02: Misma calidad visual institucional.
- RNF-PR03: Respuestas AJAX consistentes con otros perfiles.

## 5. Flujo funcional

```text
Login RECTOR → Dashboard consulta
  → Avisos (solo published)
  → Notificaciones (leer / marcar leída)
  → Reportes (si permiso) → ver/export limitado
  → Perfil → editar datos permitidos
```

### Denegación
```text
RECTOR solicita /admin/roles → Middleware 403 → security.log
```

## 6. Reglas de negocio

- RN-PR01: Capacidades efectivas = intersección rol RECTOR + permisos en `role_permissions`.
- RN-PR02: Por defecto el seeder otorga: `announcements.view`, `notifications.view`, `files.download`, `reports.view` (lectura).
- RN-PR03: No ve borradores ni avisos archivados (salvo permiso especial).
- RN-PR04: Reportes no incluyen datos sensibles (passwords, tokens, secretos).
- RN-PR05: Marcar leídas está permitido (es acción sobre su propio estado de lectura).
- RN-PR06: Cualquier intento de mutación admin se rechaza en backend.

## 7. Estructura de datos

Misma base que el sistema:
- Lectura: `announcements`, `notifications`, `notification_reads`, `attachments`, `settings` (keys públicas).
- Condicional: `users` (si `users.view`), vistas/reportes.
- Escritura limitada: `users` (perfil propio), `notification_reads`.

### Settings institucionales visibles
```text
institution_name, address, phone, email_contact, mission, vision
```

## 8. Validaciones

- Sesión y rol/permisos en cada endpoint.
- IDs de reportes en whitelist (`users_summary`, `announcements_stats`, etc.).
- Parámetros de fecha de reportes coherentes.
- Perfil: mismas reglas de campos permitidos que DOCENTE/ADMIN-self.
- CSRF en mark-read y update profile.

## 9. Seguridad

- Separar rutas `/rector/*` o reutilizar módulos con Policy.
- Negar por defecto; permitir por permiso.
- Prevenir IDOR en reportes y descargas.
- No confiar en ocultar botones: el API debe fallar con 403.
- Auditoría de descargas y accesos a reportes (FILE_DOWNLOADED, REPORT_VIEWED).

## 10. Interfaz

- Sidebar: Dashboard, Avisos, Notificaciones, Información institucional, Reportes (si aplica), Perfil.
- Cards de avisos con prioridad.
- Tablas solo-lectura cuando haya listados.
- Badges de no leídas.
- Sin entradas a Roles/Permisos/Config/Auditoría (a menos que permiso futuro).

## 11. AJAX requerido

| Endpoint | Método | Uso |
|----------|--------|-----|
| `/ajax/announcements/list` | GET | Avisos visibles |
| `/ajax/notifications/list` | GET | Notificaciones |
| `/ajax/notifications/mark-read` | POST | Marcar leída |
| `/ajax/reports/{type}` | GET | Reportes autorizados |
| `/ajax/profile/update` | POST | Perfil |
| `/ajax/institution/info` | GET | Datos institucionales |

## 12. Respuestas esperadas

**Reporte autorizado:**
```json
{
  "success": true,
  "data": {
    "type": "announcements_stats",
    "rows": [{ "status": "published", "total": 12 }]
  }
}
```

**Sin permiso reports:**
```json
{
  "success": false,
  "message": "No autorizado para ver reportes",
  "code": "FORBIDDEN"
}
```

## 13. Manejo de errores

- 401 sesión expirada.
- 403 sin permiso (mensaje neutro).
- 404 reporte inexistente en whitelist.
- Fallos DB → log + mensaje genérico.
- Export vacío → 200 con lista vacía, no error.

## 14. Casos de prueba

| ID | Caso | Esperado |
|----|------|----------|
| REC-001 | Login rector | Dashboard consulta |
| REC-002 | Ver aviso publicado | OK |
| REC-003 | Ver borrador | No visible |
| REC-004 | Marcar notificación leída | OK |
| REC-005 | Acceso `/admin/users` | 403 |
| REC-006 | Reportes con permiso | OK |
| REC-007 | Reportes sin permiso | 403 |
| REC-008 | Descargar adjunto | OK + audit |
| REC-009 | Intentar POST publicar aviso | 403 |
| REC-010 | Editar teléfono propio | OK |

## 15. Criterios de aceptación

- [ ] RECTOR consulta avisos, notificaciones e información institucional.
- [ ] Reportes solo con permiso.
- [ ] Sin acceso administrativo por defecto.
- [ ] Backend aplica RBAC en todos los endpoints.
- [ ] Perfil editable solo en campos permitidos.
- [ ] UX clara de “solo consulta”.

## 16. Dependencias

- `spec/09-roles-permisos.md`, `spec/10-avisos.md`, `spec/11-notificaciones.md`, `spec/13-dashboard.md`, `spec/19-seeders.md`
- Skills: `authorization`, `announcements`, `notifications`, `ajax`, `bootstrap-ui`

## 17. SKILL requerida

```text
skills/profile-rector/SKILL.md
```

## 18. Estado de implementación

**PENDING**
