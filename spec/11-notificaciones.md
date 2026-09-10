# SPEC 11 — Notificaciones

**Proyecto:** Unidad Educativa Fiscomisional San Lorenzo (SISTEMA_G_TECNICO_SAN_LORENZO)  
**Metodología:** POLKDEV — SPEC → SKILL → CODE  
**Estado de implementación:** PENDING

---

## 1. Objetivo

Especificar el módulo de notificaciones internas: visualización en cards, control de lectura, prioridades y soporte de adjuntos vinculados a avisos u otras fuentes.

## 2. Alcance

### Incluye
- Listado de notificaciones del usuario autenticado en cards.
- Marcar una / todas como leídas.
- Badge contador en navbar.
- Prioridad visual.
- Detalle con cuerpo y adjuntos.
- Fan-out desde avisos publicados.

### No incluye
- Push browser / email / SMS.
- WebSockets en tiempo real (polling opcional suave).

## 3. Actores

| Actor | Acciones |
|-------|----------|
| Cualquier autenticado | Ver y marcar propias |
| ADMIN / gestores | `notifications.manage` (reenviar, eliminar sistema) |
| Sistema | Crea notificaciones al publicar avisos |

## 4. Requisitos

1. Cada notificación pertenece a un `user_id` destinatario.
2. Cards muestran: título, preview body, prioridad, fecha, estado leída/no leída, indicadores de adjunto.
3. Marcar leída escribe `notification_reads` (o `read_at`).
4. Marcar todas las no leídas del usuario.
5. Contador navbar = count no leídas.
6. Prioridad: low | medium | high con badge.
7. Si hay `announcement_id`, link al aviso.
8. Adjuntos heredados del aviso o propios (`attachments`).
9. Permisos: `notifications.view`, `notifications.mark_read`, `notifications.manage`.
10. Orden default: no leídas primero, luego `created_at DESC`.

## 5. Flujo funcional

### Fan-out (desde aviso)
```text
Announcement published
→ resolver users audiencia (únicos)
→ INSERT notifications (user_id, announcement_id, title, body, priority)
→ (no crear reads aún)
```

### Inbox
```text
GET /notifications o /ajax/notifications
→ lista del user sesión
→ render cards
```

### Marcar leída
```text
POST /ajax/notifications/{id}/read
→ verificar ownership o manage
→ upsert notification_reads
→ devolver nuevo unread_count
```

### Marcar todas
```text
POST /ajax/notifications/read-all
→ insert reads faltantes
→ unread_count=0
```

## 6. Reglas de negocio

1. Usuario solo ve sus notificaciones (salvo manage).
2. Marcar leída es idempotente.
3. Eliminar aviso puede SET NULL announcement_id pero conservar notificación histórica.
4. Notificación high permanece destacada aunque esté leída (estilo distinto no-leída).
5. No crear notificaciones duplicadas al mismo user+announcement en el mismo publish (unique lógico en servicio).

## 7. Estructura de datos

Tablas: `notifications`, `notification_reads`, `attachments`, `announcements`.

**Item card JSON:**
```json
{
  "id": 90,
  "title": "Reunión de área",
  "body": "Convocatoria viernes 10:00",
  "priority": "high",
  "is_read": false,
  "created_at": "2026-09-08 15:00:00",
  "announcement_id": 15,
  "has_attachments": true
}
```

## 8. Validaciones

- `id` de notificación existe y pertenece al usuario (o manage).
- mark-all solo afecta user sesión.
- Paginación: page >= 1, per_page <= 50.

## 9. Seguridad

- Ownership check obligatorio.
- CSRF en mark read.
- Escape en título/body.
- No exponer notificaciones de otros en IDOR (probar con id ajeno → 403/404).

## 10. Interfaz

- Página Notificaciones: grid/stack de cards Bootstrap.
- Card no leída: fondo `--pi-sky-100` o barra lateral azul.
- Badge prioridad: high danger/warning, medium primary, low secondary.
- Navbar bell + badge numérico.
- Botón “Marcar todas como leídas”.
- Empty state: “No tiene notificaciones”.

## 11. AJAX requerido

| Endpoint | Método | Uso |
|----------|--------|-----|
| `/ajax/notifications` | GET | Inbox paginado |
| `/ajax/notifications/unread-count` | GET | Badge |
| `/ajax/notifications/{id}/read` | POST | Marcar una |
| `/ajax/notifications/read-all` | POST | Marcar todas |
| `/ajax/notifications/{id}` | GET | Detalle + adjuntos meta |

## 12. Respuestas esperadas

**Mark read 200:**
```json
{
  "success": true,
  "message": "Notificación marcada como leída",
  "data": { "id": 90, "is_read": true, "unread_count": 3 },
  "errors": {}
}
```

**Unread count 200:**
```json
{
  "success": true,
  "message": "OK",
  "data": { "unread_count": 3 },
  "errors": {}
}
```

## 13. Manejo de errores

| Caso | HTTP |
|------|------|
| IDOR | 403 o 404 |
| Ya leída | 200 idempotente |
| Sin auth | 401 |
| Sin permiso view | 403 |

## 14. Casos de prueba

| ID | Caso | Esperado |
|----|------|----------|
| NOT-01 | Publish aviso 2 docentes | 2 notificaciones |
| NOT-02 | Card no leída destacada | Estilo distinto |
| NOT-03 | Mark read | Contador -1 |
| NOT-04 | Read-all | Contador 0 |
| NOT-05 | IDOR otra notif | 403/404 |
| NOT-06 | Adjunto desde aviso | Lista en detalle |
| NOT-07 | Orden no leídas primero | Correcto |
| NOT-08 | Badge navbar sync AJAX | Coincide count |

## 15. Criterios de aceptación

- [ ] Cards de notificaciones implementadas.
- [ ] Lectura individual y masiva funcional.
- [x] Badge unread correcto.
- [ ] Prioridades visibles.
- [ ] Adjuntos accesibles según SPEC 12.
- [ ] Protección IDOR.
- [ ] Integración fan-out con avisos.

## 16. Dependencias

- SPEC 06 (UI cards), 09, 10, 12.
- Tablas notifications / notification_reads.

## 17. SKILL requerida

- `skills/notifications/SKILL.md`
- `skills/announcements/SKILL.md`
- `skills/bootstrap-ui/SKILL.md`
- `skills/ajax/SKILL.md`

## 18. Estado de implementación

**PENDING**
