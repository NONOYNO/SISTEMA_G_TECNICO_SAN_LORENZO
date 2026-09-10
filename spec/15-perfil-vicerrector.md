# SPEC 15 — Perfil VICERRECTOR

**Proyecto:** Unidad Educativa Fiscomisional San Lorenzo  
**Stack:** PHP 8 + MySQL + PDO + MVC + Bootstrap 5 + AJAX + XAMPP  
**Módulo:** Gestión editorial del vicerrector  
**Estado:** PENDING

---

## 1. Objetivo

Habilitar al rol VICERRECTOR para crear, editar, publicar y archivar avisos institucionales; gestionar notificaciones; adjuntar archivos; y consultar sus propias publicaciones, como actor principal de comunicación interna.

## 2. Alcance

**Incluye:**
- Panel VICERRECTOR y dashboard de publicaciones.
- CRUD de avisos (borrador → publicado → archivado).
- Creación de notificaciones vinculadas o independientes.
- Carga, listado y eliminación controlada de adjuntos.
- Filtros por estado, categoría, prioridad, fechas.
- Vista de perfil propio (campos permitidos).

**Excluye:**
- Administración de roles/permisos globales.
- Gestión total de usuarios (salvo consulta limitada si hay permiso).
- Configuración del sistema y auditoría completa (solo eventos propios si se permite).

## 3. Actores

| Actor | Rol en el módulo |
|-------|------------------|
| VICERRECTOR | Editor/publicador principal |
| ADMIN | Supervisión y override |
| RECTOR / DOCENTE | Solo lectura de lo publicado (otros SPEC) |

## 4. Requisitos

### Funcionales
- RF-PV01: Crear aviso con título, descripción, contenido, categoría, prioridad, fechas, estado, destinatarios.
- RF-PV02: Guardar como `draft` sin notificar.
- RF-PV03: Publicar (`published`) y opcionalmente disparar notificaciones a destinatarios.
- RF-PV04: Archivar (`archived`) — deja de mostrarse a docentes como vigente.
- RF-PV05: Editar avisos propios; editar ajenos solo con permiso `announcements.edit` global.
- RF-PV06: Adjuntar archivos permitidos al aviso.
- RF-PV07: Crear notificaciones con título, mensaje, prioridad, destinatarios.
- RF-PV08: Consultar listado de sus publicaciones con métricas (vistas/lecturas si existen).
- RF-PV09: Eliminar borradores propios según reglas; no borrar publicados sin permiso `announcements.delete`.
- RF-PV10: Perfil: editar datos permitidos (igual política que usuario autenticado).

### No funcionales
- RNF-PV01: Formularios Bootstrap 5 con validación cliente + servidor.
- RNF-PV02: Upload con barra de progreso AJAX cuando sea viable.
- RNF-PV03: Paginación y búsqueda AJAX.

## 5. Flujo funcional

### Publicar aviso
```text
1. VICERRECTOR → Avisos → Nuevo
2. Completa formulario + adjuntos (opcional)
3. Guarda borrador O publica directamente
4. Si publica:
   - status = published
   - published_at = now
   - (opcional) genera notifications por destinatario
   - audit ANNOUNCEMENT_PUBLISHED
5. Destinatarios ven el aviso en sus paneles
```

### Archivar
```text
Listado → Archivar → confirma modal → status=archived → audit
```

## 6. Reglas de negocio

- RN-PV01: Estados válidos: `draft | published | archived`. Transiciones: draft→published, published→archived, draft→archived; archived no vuelve a published sin permiso especial.
- RN-PV02: `publish_at` no puede ser posterior a `expires_at` si ambas existen.
- RN-PV03: Título único no obligatorio; contenido no vacío al publicar.
- RN-PV04: Prioridad: `low | normal | high | urgent`.
- RN-PV05: Destinatarios: roles, usuarios o “todos los activos”.
- RN-PV06: Al publicar, solo se notifican usuarios activos.
- RN-PV07: Archivos: extensiones whitelist (ver `spec/12-archivos.md`); máx. tamaño configurable (ej. 10 MB).
- RN-PV08: Ejecutables prohibidos (.php, .exe, .bat, etc.).
- RN-PV09: Soft-delete preferible a hard-delete en publicados.

## 7. Estructura de datos

### `announcements` (campos clave)
| Campo | Tipo | Notas |
|-------|------|-------|
| id | BIGINT PK | |
| title | VARCHAR(200) | required |
| description | VARCHAR(500) | |
| content | TEXT | |
| category | VARCHAR(100) | |
| priority | ENUM | |
| status | ENUM | draft/published/archived |
| publish_at | DATETIME NULL | |
| expires_at | DATETIME NULL | |
| created_by | FK users | |
| updated_by | FK users | |
| created_at / updated_at | DATETIME | |

### Relacionadas
- `announcement_recipients` (opcional): announcement_id, recipient_type, recipient_id
- `notifications`, `attachments`, `audit_logs`

## 8. Validaciones

- Título: 5–200 caracteres.
- Contenido al publicar: mín. 10 caracteres.
- Categoría y prioridad en catálogos permitidos.
- Fechas coherentes.
- Destinatarios no vacíos al publicar con notificación.
- CSRF en create/update/publish/archive/upload.
- Validación MIME + extensión + tamaño en adjuntos.

## 9. Seguridad

- Permisos: `announcements.create|edit|delete|publish`, `notifications.create`, `files.upload|delete`.
- Autorización en cada acción AJAX.
- Almacenar uploads fuera de `public/` cuando sea posible.
- Nombres físicos UUID; nunca `$_FILES['name']` crudo.
- Escapar contenido rich-text o sanitizar HTML permitido.
- Registrar FILE_UPLOADED / FILE_DELETED / ANNOUNCEMENT_*.

## 10. Interfaz

- Sidebar: Mis avisos, Nuevo aviso, Notificaciones, Archivos, Perfil.
- Tabla responsive + filtros.
- Formulario multipestaña o secciones: Datos / Destinatarios / Adjuntos.
- Badges de estado (borrador=gris, publicado=verde, archivado=secondary).
- Modal de confirmación para publicar/archivar/eliminar.
- Preview de card como la verá el docente.

## 11. AJAX requerido

| Endpoint | Método | Uso |
|----------|--------|-----|
| `/ajax/announcements` | GET | Listar/filtrar |
| `/ajax/announcements` | POST | Crear |
| `/ajax/announcements/{id}` | PUT | Editar |
| `/ajax/announcements/{id}/publish` | POST | Publicar |
| `/ajax/announcements/{id}/archive` | POST | Archivar |
| `/ajax/notifications` | POST | Crear notificación |
| `/ajax/attachments/upload` | POST | Multipart upload |
| `/ajax/attachments/{id}` | DELETE | Eliminar adjunto |

## 12. Respuestas esperadas

**Publicación OK:**
```json
{
  "success": true,
  "message": "Aviso publicado correctamente",
  "data": {
    "id": 15,
    "status": "published",
    "notifications_created": 24
  }
}
```

**Error transición (409):**
```json
{
  "success": false,
  "message": "No se puede publicar un aviso archivado",
  "code": "INVALID_STATE"
}
```

## 13. Manejo de errores

- Upload fallido: rollback metadatos si el archivo no se guardó.
- Publicación parcial (notificaciones): transacción DB; si falla, no marcar published o compensar con log.
- 422 validación con mapa de campos.
- 403 sin permiso; 404 recurso no encontrado o no propio (mismo mensaje genérico si evita enumeración).

## 14. Casos de prueba

| ID | Caso | Esperado |
|----|------|----------|
| VIC-001 | Crear borrador | status=draft |
| VIC-002 | Publicar borrador | status=published + audit |
| VIC-003 | Archivar publicado | oculto a DOCENTE |
| VIC-004 | Adjuntar PDF | OK |
| VIC-005 | Adjuntar .php | Rechazo |
| VIC-006 | DOCENTE intenta publicar | 403 |
| VIC-007 | Fechas incoherentes | 422 |
| VIC-008 | Crear notificación masiva | Destinatarios activos notificados |
| VIC-009 | Eliminar adjunto propio | OK + audit |
| VIC-010 | Editar aviso ajeno sin permiso | 403 |

## 15. Criterios de aceptación

- [ ] VICERRECTOR gestiona ciclo de vida completo de avisos.
- [ ] Notificaciones y adjuntos funcionan con validaciones de seguridad.
- [ ] Transiciones de estado correctas y auditadas.
- [ ] DOCENTE no puede usar endpoints de publicación.
- [ ] UI clara con preview y filtros.
- [ ] AJAX cubre CRUD principal sin recargas innecesarias.

## 16. Dependencias

- `spec/10-avisos.md`, `spec/11-notificaciones.md`, `spec/12-archivos.md`, `spec/13-dashboard.md`, `spec/09-roles-permisos.md`
- Skills: `announcements`, `notifications`, `file-upload`, `ajax`, `authorization`

## 17. SKILL requerida

```text
skills/profile-vicerrector/SKILL.md
```

## 18. Estado de implementación

**PENDING**
