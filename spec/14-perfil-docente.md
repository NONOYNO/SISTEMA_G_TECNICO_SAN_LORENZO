# SPEC 14 — Perfil DOCENTE

**Proyecto:** Unidad Educativa Fiscomisional San Lorenzo  
**Stack:** PHP 8 + MySQL + PDO + MVC + Bootstrap 5 + AJAX + XAMPP  
**Módulo:** Perfil y panel del docente  
**Estado:** PENDING

---

## 1. Objetivo

Permitir al rol DOCENTE consultar avisos y notificaciones institucionales, descargar archivos autorizados, editar únicamente datos personales permitidos y marcar notificaciones como leídas, bajo control RBAC estricto.

## 2. Alcance

**Incluye:**
- Vista de perfil propio (`/perfil`).
- Listado y detalle de avisos publicados dirigidos al docente.
- Listado de notificaciones con estado leído/no leído.
- Descarga de adjuntos autorizados.
- Edición de campos permitidos del perfil.
- Marcar una o todas las notificaciones como leídas (AJAX).

**Excluye:**
- Crear/editar/publicar avisos.
- Gestión de usuarios, roles o configuración.
- Acceso a auditoría o reportes administrativos.

## 3. Actores

| Actor | Capacidad |
|-------|-----------|
| DOCENTE | Consumidor de información; editor limitado de su perfil |
| ADMIN | Puede impersonar/gestionar al docente desde módulo usuarios (fuera de este SPEC) |
| Otros roles | No usan esta vista específica; tienen su propio perfil |

## 4. Requisitos

### Funcionales
- RF-PD01: Ver perfil propio (nombres, apellidos, correo, usuario, rol, estado, foto, último acceso).
- RF-PD02: Editar campos permitidos: teléfono, foto de perfil, contraseña (con verificación actual).
- RF-PD03: No editar: rol, permisos, correo institucional (salvo política ADMIN), estado, usuario.
- RF-PD04: Listar avisos publicados vigentes dirigidos a DOCENTE o a “todos”.
- RF-PD05: Ver detalle de aviso con contenido, prioridad, fechas y adjuntos.
- RF-PD06: Listar notificaciones con badge de no leídas.
- RF-PD07: Marcar notificación como leída.
- RF-PD08: Marcar todas como leídas.
- RF-PD09: Descargar archivo solo si tiene `files.download` y el adjunto pertenece a un aviso/notificación visible.
- RF-PD10: Registrar descarga en auditoría.

### No funcionales
- RNF-PD01: Cards Bootstrap para avisos/notificaciones.
- RNF-PD02: Descarga vía endpoint controlado (no path directo público).
- RNF-PD03: Tiempo de respuesta AJAX marcar leída < 500 ms local.

## 5. Flujo funcional

### Consultar avisos
```text
Login DOCENTE → Dashboard → Avisos
  → Listado AJAX/paginado
  → Ver detalle
  → (opcional) Descargar adjunto → audit FILE_DOWNLOADED
```

### Notificaciones
```text
Abrir Notificaciones → ver cards
  → clic "Marcar leída" (AJAX)
  → actualiza badge y card
```

### Editar perfil
```text
Perfil → Editar datos permitidos → validar → guardar
  → audit USER_PROFILE_UPDATED
```

## 6. Reglas de negocio

- RN-PD01: Solo ve avisos con `status = published` y no expirados (o según `expires_at` nulo).
- RN-PD02: Destinatarios: rol DOCENTE, usuario específico o audiencia global.
- RN-PD03: Campos no permitidos se ignoran aunque se envíen por POST manipulado.
- RN-PD04: Cambio de contraseña exige contraseña actual + política de complejidad.
- RN-PD05: Foto: solo JPG/JPEG/PNG, máx. 2 MB, renombrado UUID.
- RN-PD06: Una notificación leída no vuelve a “no leída” por el docente.
- RN-PD07: Soft-delete de aviso oculta el contenido al docente.

## 7. Estructura de datos

### Tablas involucradas
- `users` — datos de perfil
- `announcements` — avisos
- `notifications` — notificaciones
- `notification_reads` — `(user_id, notification_id, read_at)`
- `attachments` — adjuntos
- `audit_logs` — eventos

### Campos editables DOCENTE

| Campo | Editable |
|-------|----------|
| nombres | No (o solo con permiso especial) |
| apellidos | No |
| email | No |
| username | No |
| phone | Sí |
| avatar | Sí |
| password | Sí (con actual) |
| role | No |

## 8. Validaciones

- Backend obligatorio en todos los POST/PUT.
- Teléfono: formato E.cuador / dígitos, longitud 7–15.
- Contraseña: mín. 8, mayúscula, minúscula, número, símbolo.
- ID de notificación/aviso: entero positivo existente y autorizado.
- CSRF token en formularios y AJAX mutantes.
- MIME real de avatar.

## 9. Seguridad

- Middleware auth + rol/permiso `announcements.view`, `notifications.view`, `files.download`.
- Prevenir IDOR: verificar ownership/audiencia antes de leer o descargar.
- Path traversal bloqueado en descargas.
- XSS: escapar título/contenido en vistas.
- PDO prepared statements.
- Rate limit básico en cambio de contraseña.

## 10. Interfaz

- Sección Perfil: card con avatar, datos, botón Editar.
- Avisos: grid de cards (prioridad, fecha, categoría, CTA Ver).
- Notificaciones: cards con badge “Nueva”; botón Marcar leída.
- Modal Bootstrap para editar perfil.
- Alertas toast para éxito/error AJAX.
- Mobile-first, sin overlay clutter.

## 11. AJAX requerido

| Endpoint | Método | Descripción |
|----------|--------|-------------|
| `/ajax/notifications/list` | GET | Listado paginado |
| `/ajax/notifications/mark-read` | POST | Marcar una leída |
| `/ajax/notifications/mark-all-read` | POST | Marcar todas |
| `/ajax/announcements/list` | GET | Avisos visibles |
| `/ajax/profile/update` | POST | Actualizar campos permitidos |
| `/ajax/files/download/{id}` | GET | Metadatos/enlace firmado o stream |

## 12. Respuestas esperadas

**Marcar leída OK:**
```json
{
  "success": true,
  "message": "Notificación marcada como leída",
  "data": { "unread_count": 3 }
}
```

**Edición perfil OK:**
```json
{
  "success": true,
  "message": "Perfil actualizado",
  "data": { "user": { "id": 10, "phone": "0999999999" } }
}
```

**Error validación (422):**
```json
{
  "success": false,
  "message": "Datos inválidos",
  "errors": { "password": ["La contraseña actual es incorrecta"] }
}
```

## 13. Manejo de errores

- Archivo no autorizado: 403 + log seguridad.
- Notificación inexistente: 404 genérico.
- Fallo upload avatar: mensaje claro, sin rutas internas.
- Errores DB: log `database.log`, respuesta genérica.

## 14. Casos de prueba

| ID | Caso | Esperado |
|----|------|----------|
| DOC-001 | Ver avisos publicados | Lista visible |
| DOC-002 | Aviso borrador | No visible |
| DOC-003 | Marcar leída | Badge decrementa |
| DOC-004 | Marcar todas | unread=0 |
| DOC-005 | Descargar adjunto autorizado | Archivo + audit |
| DOC-006 | Descargar adjunto ajeno | 403 |
| DOC-007 | Editar teléfono | OK |
| DOC-008 | Intentar cambiar rol vía POST | Ignorado/403 |
| DOC-009 | Contraseña débil | 422 |
| DOC-010 | Sin sesión | Redirect/401 |

## 15. Criterios de aceptación

- [ ] DOCENTE consulta avisos y notificaciones correctamente.
- [ ] Puede marcar leídas (una/todas) vía AJAX.
- [ ] Descarga solo archivos autorizados con auditoría.
- [ ] Solo edita campos permitidos.
- [ ] UI en cards responsive.
- [ ] Sin escalada de privilegios.

## 16. Dependencias

- `spec/07-autenticacion.md`, `spec/08-usuarios.md`, `spec/10-avisos.md`, `spec/11-notificaciones.md`, `spec/12-archivos.md`, `spec/13-dashboard.md`
- Skills: `users`, `announcements`, `notifications`, `file-upload`, `ajax`

## 17. SKILL requerida

```text
skills/profile-docente/SKILL.md
```

## 18. Estado de implementación

**PENDING**
