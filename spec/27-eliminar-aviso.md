# SPEC 27 — Eliminar aviso (Avisos institucionales)

**Proyecto:** Unidad Educativa Fiscomisional San Lorenzo (PortalInfor)  
**Metodología:** POLKDEV — SPEC → SKILL → CODE  
**Estado de implementación:** COMPLETED  
**SKILL requerida:** `skills/announcements/SKILL.md`  
**Dependencias:** `spec/10-avisos.md`, `spec/12-archivos.md`, `spec/22-auditoria.md`

---

## 1. Objetivo

Permitir eliminar un aviso institucional desde el módulo **Avisos institucionales**, con confirmación, autorización `announcements.delete`, limpieza de adjuntos/notificaciones asociadas y registro de auditoría.

## 2. Alcance

### Incluye
- Acción **Eliminar** en el listado (tabla de gestión) y en la ficha del aviso.
- Confirmación explícita antes de borrar.
- Borrado en backend (POST + CSRF + permiso).
- Eliminación de adjuntos en disco y BD; notificaciones ligadas al aviso.
- Flash de éxito/error.

### No incluye
- Papelera / soft-delete (fuera de alcance; archivar sigue disponible).
- Eliminación masiva por lote.

## 3. Actores

| Actor | Puede eliminar |
|-------|----------------|
| ADMIN | Sí (`announcements.delete`) |
| VICERRECTOR | Sí |
| RECTOR / DOCENTE | No (sin permiso; UI oculta + 403) |

## 4. Requisitos

1. Ruta: `POST /announcements/{id}/delete` con middleware `auth`, `csrf`, `permission:announcements.delete`.
2. UI listado: botón/ícono **Eliminar** por fila (solo si `auth_can('announcements.delete')`).
3. UI detalle: mantener/mejorar botón Eliminar con confirmación.
4. Confirmación: mensaje claro (“¿Eliminar el aviso «…»? Esta acción no se puede deshacer.”).
5. Al eliminar: borrar aviso, adjuntos físicos + filas `attachments`, notificaciones del aviso (lecturas en cascada).
6. Auditoría: `announcements.delete` con título/estado previos.
7. Redirigir a `/announcements` con mensaje de éxito.

## 5. Flujo funcional

1. Usuario con permiso abre **Avisos institucionales**.
2. En la fila o ficha pulsa **Eliminar**.
3. Confirma en diálogo.
4. POST al servidor → validación permiso/CSRF → servicio elimina → audit → redirect listado.

## 6. Reglas de negocio

1. Sin permiso `announcements.delete` → no mostrar botón; backend 403.
2. Aviso inexistente → error amigable y redirect.
3. Fallo parcial de archivo en disco → registrar error y continuar limpieza BD cuando sea seguro (o abortar transacción).
4. Preferir transacción DB; archivos se borran tras commit o con rollback de filas si falla move.

## 7. Estructura de datos

- `DELETE FROM announcements WHERE id = ?`
- `attachments` donde `attachable_type='announcement' AND attachable_id=?`
- `DELETE FROM notifications WHERE announcement_id=?` (reads CASCADE)

## 8. Validaciones

- ID numérico positivo.
- Existencia del aviso.
- Permiso y CSRF.

## 9. Seguridad

- Authz backend obligatoria.
- CSRF.
- Path traversal al borrar archivos (solo bajo `storage/uploads`).
- No confiar en ocultar el botón.

## 10. Interfaz

- Listado gestión: botón `btn-outline-danger` “Eliminar”.
- Confirmación `confirm()` o modal Bootstrap.
- Icono `bi-trash` opcional.

## 11. AJAX requerido

No obligatorio; form POST suficiente.

## 12. Respuestas esperadas

- Éxito: flash success + redirect `/announcements`
- Error: flash error + redirect listado o ficha

## 13. Manejo de errores

- PDO/FK: mensaje genérico, log en `logs/php-error.log`
- Archivo ausente en disco: no bloquear borrado de registro

## 14. Casos de prueba

| ID | Caso | Esperado |
|----|------|----------|
| DEL-01 | ADMIN elimina desde listado | Aviso desaparece |
| DEL-02 | VICERRECTOR elimina | OK |
| DEL-03 | DOCENTE | Sin botón / 403 |
| DEL-04 | Cancelar confirmación | No borra |
| DEL-05 | Aviso con adjuntos | Archivos y filas eliminados |
| DEL-06 | Aviso publicado | Notificaciones del aviso eliminadas |
| DEL-07 | Audit | Evento `announcements.delete` |

## 15. Criterios de aceptación

- [x] Eliminar visible en listado Avisos institucionales
- [x] Eliminar en ficha del aviso
- [x] Confirmación antes de POST
- [x] Permiso + CSRF
- [x] Limpieza adjuntos y notificaciones
- [x] Auditoría
- [x] SPEC + SKILL actualizadas

## 16. Dependencias

- `AnnouncementController::destroy`
- `AnnouncementService::delete`
- `FileUploadService` (borrado de adjuntos)
- Ruta ya definida en `web.php`

## 17. SKILL requerida

`skills/announcements/SKILL.md`

## 18. Estado de implementación

COMPLETED
