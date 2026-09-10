# SPEC 13 — Dashboards por rol

**Proyecto:** Unidad Educativa Fiscomisional San Lorenzo  
**Stack:** PHP 8 + MySQL + PDO + MVC + Bootstrap 5 + AJAX + XAMPP  
**Módulo:** Dashboard  
**Estado:** PENDING

---

## 1. Objetivo

Definir e implementar dashboards diferenciados por rol (ADMIN, VICERRECTOR, RECTOR, DOCENTE) que muestren indicadores, accesos rápidos y actividad relevante según permisos RBAC, sin exponer datos a los que el usuario no tenga autorización.

## 2. Alcance

**Incluye:**
- Vista principal post-login (`/dashboard`).
- Widgets/indicadores por rol.
- Carga AJAX de contadores y actividad reciente.
- Enlaces de acción rápida según permisos.
- Diseño responsive (desktop, tablet, mobile).

**Excluye:**
- CRUD completo de usuarios, avisos o auditoría (pertenecen a sus módulos).
- Configuración global del sistema.
- Generación de reportes PDF/Excel (solo accesos a reportes autorizados).

## 3. Actores

| Actor | Acceso al dashboard |
|-------|---------------------|
| ADMIN | Completo: usuarios, avisos, auditoría, actividad |
| VICERRECTOR | Publicaciones: borradores, publicados, archivados, adjuntos |
| RECTOR | Consulta: avisos, notificaciones, estadísticas autorizadas |
| DOCENTE | Consumo: avisos recientes, notificaciones no leídas, archivos |
| Invitado | Sin acceso; redirección a login |

## 4. Requisitos

### Funcionales
- RF-D01: Tras login exitoso, redirigir a `/dashboard`.
- RF-D02: Renderizar layout según rol primario del usuario.
- RF-D03 ADMIN: mostrar totales de usuarios activos/inactivos, avisos por estado, notificaciones enviadas, últimos eventos de auditoría.
- RF-D04 VICERRECTOR: mostrar conteo de borradores, publicados, archivados; avisos propios; notificaciones creadas; archivos adjuntos recientes.
- RF-D05 RECTOR: mostrar avisos publicados vigentes, notificaciones recibidas, resumen institucional autorizado.
- RF-D06 DOCENTE: mostrar avisos recientes, notificaciones no leídas (badge), archivos disponibles para descarga.
- RF-D07: Cada widget solo visible si el usuario tiene el permiso correspondiente.
- RF-D08: Actualizar contadores vía AJAX sin recargar la página completa.

### No funcionales
- RNF-D01: Tiempo de carga inicial < 2 s en red local XAMPP.
- RNF-D02: Compatible Bootstrap 5, sidebar + navbar.
- RNF-D03: Paleta institucional (celeste, azul, blanco).

## 5. Flujo funcional

```text
1. Usuario autenticado solicita GET /dashboard
2. Middleware verifica sesión activa y timeout
3. Sistema resuelve rol(es) y permisos
4. Controller selecciona vista parcial por rol
5. Services obtienen métricas autorizadas (PDO)
6. Vista renderiza cards/indicadores
7. JS inicia AJAX para refrescar contadores (opcional periódico)
8. Usuario hace clic en acción rápida → navega al módulo destino
```

## 6. Reglas de negocio

- RN-D01: Un usuario con múltiples roles usa el rol de mayor privilegio para el layout base; los widgets se filtran por unión de permisos.
- RN-D02: Contadores de auditoría solo para `audit.view`.
- RN-D03: DOCENTE y RECTOR no ven listados administrativos de usuarios salvo permiso explícito.
- RN-D04: Datos de métricas deben respetar soft-delete / estado activo.
- RN-D05: Avisos expirados no cuentan como “vigentes” en widgets de consulta.
- RN-D06: Si no hay datos, mostrar estado vacío amigable (no error).

## 7. Estructura de datos

### Fuentes (consulta, no tablas nuevas obligatorias)

| Origen | Uso |
|--------|-----|
| `users` | Conteos ADMIN |
| `announcements` | Estados: draft, published, archived |
| `notifications` + `notification_reads` | No leídas por usuario |
| `attachments` | Archivos recientes |
| `audit_logs` | Actividad reciente ADMIN |

### Respuesta de métricas (JSON conceptual)

```json
{
  "role": "ADMIN",
  "metrics": {
    "users_active": 42,
    "users_inactive": 3,
    "announcements_published": 12,
    "announcements_draft": 2,
    "notifications_total": 85,
    "audit_last_24h": 17
  },
  "recent": []
}
```

## 8. Validaciones

- Sesión válida y usuario `status = active`.
- Rol reconocido; si no, denegar con 403.
- Parámetros AJAX: solo filtros permitidos (`period=24h|7d|30d`).
- No aceptar IDs de entidad ajenos sin autorización (evitar IDOR en “actividad reciente”).

## 9. Seguridad

- Autenticación obligatoria.
- Autorización por permiso en backend (no solo ocultar widgets).
- CSRF en cualquier acción POST desde el dashboard.
- Escapar salida HTML (XSS).
- PDO prepared statements.
- No exponer stack traces ni SQL en errores.

## 10. Interfaz

- Layout: navbar + sidebar + área de contenido.
- Cards Bootstrap 5 con iconos (Font Awesome).
- Badges de prioridad/estado.
- Breadcrumb: Inicio / Dashboard.
- Responsive: cards en 1 columna en mobile, 2–4 en desktop.
- ADMIN: tabla compacta de últimos 10 `audit_logs`.
- DOCENTE: lista de cards de avisos/notificaciones.

## 11. AJAX requerido

| Endpoint | Método | Descripción |
|----------|--------|-------------|
| `/ajax/dashboard/metrics` | GET | Contadores según rol |
| `/ajax/dashboard/recent-activity` | GET | Actividad reciente autorizada |
| `/ajax/dashboard/unread-count` | GET | Badge notificaciones no leídas |

Headers: `X-Requested-With: XMLHttpRequest`, token CSRF si aplica a mutaciones.

## 12. Respuestas esperadas

**Éxito (200):**
```json
{
  "success": true,
  "data": { "metrics": {}, "recent": [] },
  "message": "OK"
}
```

**No autenticado (401):**
```json
{ "success": false, "message": "Sesión expirada", "code": "UNAUTHENTICATED" }
```

**Sin permiso (403):**
```json
{ "success": false, "message": "No autorizado", "code": "FORBIDDEN" }
```

## 13. Manejo de errores

- Fallo de consulta: log en `logs/application.log`; mensaje genérico al usuario.
- Sesión expirada: redirección a login o JSON 401 para AJAX.
- Rol desconocido: 403 + registro en `security.log`.
- Timeout de DB: reintentar una vez; luego error controlado.

## 14. Casos de prueba

| ID | Caso | Esperado |
|----|------|----------|
| DASH-001 | Login ADMIN | Dashboard ADMIN con widgets de usuarios y auditoría |
| DASH-002 | Login VICERRECTOR | Widgets de publicaciones, sin auditoría |
| DASH-003 | Login RECTOR | Solo consulta/estadísticas autorizadas |
| DASH-004 | Login DOCENTE | Avisos y notificaciones no leídas |
| DASH-005 | Sin sesión → `/dashboard` | Redirect login |
| DASH-006 | DOCENTE pide `/ajax/dashboard/recent-activity` audit | 403 |
| DASH-007 | Métricas AJAX ADMIN | JSON success con conteos |
| DASH-008 | Usuario inactivo | Bloqueo, sin dashboard |

## 15. Criterios de aceptación

- [ ] Cada rol ve un dashboard distinto y coherente.
- [ ] Widgets respetan permisos backend.
- [ ] AJAX de métricas funciona sin recarga completa.
- [ ] UI responsive e institucional.
- [ ] Sin fugas de datos entre roles.
- [ ] Estado de implementación documentado como PENDING hasta código aprobado.

## 16. Dependencias

- `spec/07-autenticacion.md`
- `spec/09-roles-permisos.md`
- `spec/10-avisos.md`
- `spec/11-notificaciones.md`
- `spec/22-auditoria.md`
- Skills: `authentication`, `authorization`, `bootstrap-ui`, `ajax`

## 17. SKILL requerida

```text
skills/dashboard/SKILL.md
```

(Complementaria: `skills/bootstrap-ui/SKILL.md`, `skills/ajax/SKILL.md`)

## 18. Estado de implementación

**PENDING**
