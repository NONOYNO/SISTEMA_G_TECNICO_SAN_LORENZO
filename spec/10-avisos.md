# SPEC 10 — Avisos (Announcements)

**Proyecto:** Unidad Educativa Fiscomisional San Lorenzo (SISTEMA_G_TECNICO_SAN_LORENZO)  
**Metodología:** POLKDEV — SPEC → SKILL → CODE  
**Estado de implementación:** PENDING

---

## 1. Objetivo

Especificar el módulo de avisos institucionales: CRUD, estados Borrador/Publicado/Archivado, campos de contenido y metadatos, y generación de notificaciones a destinatarios al publicar.

## 2. Alcance

### Incluye
- Crear, editar, listar, ver, publicar, archivar avisos.
- Campos: título, descripción, contenido, categoría, prioridad, fechas, destinatarios.
- Adjuntos opcionales (integra SPEC 12).
- Disparo de notificaciones (SPEC 11) al pasar a `published`.

### No incluye
- Editor colaborativo en tiempo real.
- Programación compleja de campañas recurrentes (solo `publish_at` / `expire_at`).

## 3. Actores

| Actor | Acciones |
|-------|----------|
| ADMIN / RECTOR / VICERRECTOR | CRUD + publish según permisos |
| DOCENTE | Ver avisos publicados dirigidos a su audiencia |
| Autor | Editar borradores propios (regla configurable) |

## 4. Requisitos

1. Estados: `draft` | `published` | `archived`.
2. Campos obligatorios al publicar: título, contenido, destinatarios, prioridad.
3. `description` resumen corto para cards.
4. `category` libre o catálogo simple (Académico, Administrativo, Eventos, Urgente).
5. `priority`: low | medium | high.
6. `publish_at` / `expire_at` opcionales; si `publish_at` futuro, puede permanecer draft hasta job manual o cron simple (fase 1: publicación manual).
7. Destinatarios (`audience`): roles y/o user_ids.
8. Al publicar: crear `notifications` por cada destinatario resuelto.
9. Permisos: `announcements.view|create|update|publish|delete`.
10. Listados filtrables por estado, categoría, prioridad, autor.

## 5. Flujo funcional

### Crear borrador
```text
POST announcements { status: draft, ... }
→ validar mínimos título
→ guardar
→ audit announcements.create
```

### Publicar
```text
POST /announcements/{id}/publish
→ validar campos completos
→ status=published, publish_at=now (si null)
→ resolver audience → users
→ NotificationService::fanOut(announcement, users)
→ audit announcements.publish
```

### Archivar
```text
POST /announcements/{id}/archive → status=archived
→ opcional: ocultar de feeds docentes
```

### Lectura DOCENTE
```text
GET /announcements (solo published, no expired, audience match)
→ cards / detalle
```

## 6. Reglas de negocio

1. `draft` invisible a destinatarios finales.
2. `archived` no aparece en feed principal; visible en histórico a gestores.
3. Si `expire_at < now`, tratar como no vigente en feed docente.
4. Republicar aviso archivado requiere permiso publish (pasa a published).
5. Editar aviso ya published: permitido a gestores; si cambia contenido sustancial, audit; no duplicar notificaciones automáticamente salvo flag “notificar actualización”.
6. Eliminación física solo ADMIN o soft via archived.
7. Audience vacía ⇒ no se puede publicar.

## 7. Estructura de datos

Tabla `announcements` (SPEC 04).

**Audience ejemplo JSON:**
```json
{
  "roles": ["DOCENTE", "VICERRECTOR"],
  "user_ids": [12, 18]
}
```

**Payload create/update:**
```json
{
  "title": "Reunión de área",
  "description": "Convocatoria viernes 10:00",
  "content": "Se convoca a todo el personal docente...",
  "category": "Académico",
  "priority": "high",
  "status": "draft",
  "publish_at": null,
  "expire_at": "2026-09-30 23:59:59",
  "audience": { "roles": ["DOCENTE"], "user_ids": [] },
  "_csrf": "..."
}
```

Adjuntos: `attachments` con `attachable_type=announcement`.

## 8. Validaciones

| Campo | Regla |
|-------|-------|
| title | required, max 200 |
| description | optional, max 500 |
| content | required para publish, max 20000 |
| category | optional, max 100 |
| priority | enum |
| status | enum |
| publish_at / expire_at | datetime; expire >= publish si ambos |
| audience | required para publish; JSON válido |

## 9. Seguridad

- Autorización por permiso en cada acción.
- Escape XSS en título/contenido al renderizar.
- Si se permite HTML en content: sanitizer allowlist estricto.
- CSRF en mutaciones.
- No filtrar IDs de borradores ajenos a DOCENTE.

## 10. Interfaz

- Listado gestores: tabla con badges de estado/prioridad.
- Feed docentes: cards (título, descripción, categoría, fecha, prioridad).
- Formulario: tabs o secciones Datos / Destinatarios / Adjuntos.
- Botones: Guardar borrador, Publicar, Archivar (confirm).
- Filtros toolbar.

## 11. AJAX requerido

| Endpoint | Método | Uso |
|----------|--------|-----|
| `/ajax/announcements` | GET | Listar/filtrar |
| `/ajax/announcements` | POST | Crear |
| `/ajax/announcements/{id}` | POST/PUT | Actualizar |
| `/ajax/announcements/{id}/publish` | POST | Publicar + fan-out |
| `/ajax/announcements/{id}/archive` | POST | Archivar |
| `/ajax/announcements/{id}/attachments` | POST | Subir adjunto |

## 12. Respuestas esperadas

**Publish 200:**
```json
{
  "success": true,
  "message": "Aviso publicado",
  "data": {
    "id": 15,
    "status": "published",
    "notifications_created": 42
  },
  "errors": {}
}
```

**Publish sin audience 400:**
```json
{
  "success": false,
  "message": "Debe indicar destinatarios",
  "data": null,
  "errors": { "audience": ["Campo obligatorio para publicar"] }
}
```

## 13. Manejo de errores

| Caso | Manejo |
|------|--------|
| Publicar draft incompleto | 400 errors |
| Fan-out parcial BD | transacción: rollback publish si notif falla |
| Sin permiso publish | 403 |
| Aviso no encontrado | 404 |

## 14. Casos de prueba

| ID | Caso | Esperado |
|----|------|----------|
| ANN-01 | Crear draft | status draft |
| ANN-02 | DOCENTE no ve draft | Oculto |
| ANN-03 | Publicar a DOCENTE | N notificaciones |
| ANN-04 | Archivar | Sale de feed |
| ANN-05 | expire_at pasado | No vigente |
| ANN-06 | Publish sin título | 400 |
| ANN-07 | Adjunto PDF en aviso | Download OK |
| ANN-08 | VICERRECTOR sin delete | 403 delete |

## 15. Criterios de aceptación

- [ ] CRUD + estados draft/published/archived.
- [ ] Campos requeridos presentes y validados.
- [ ] Audience por roles/users.
- [ ] Publish genera notificaciones en transacción.
- [ ] Feed docente solo publicados vigentes.
- [ ] AJAX publish/archive operativos.
- [ ] Auditoría de create/publish/archive.

## 16. Dependencias

- SPEC 04, 05, 09, 11, 12.
- NotificationService, FileUploadService.

## 17. SKILL requerida

- `skills/announcements/SKILL.md`
- `skills/notifications/SKILL.md`
- `skills/file-upload/SKILL.md`
- `skills/ajax/SKILL.md`

## 18. Estado de implementación

**PENDING**
