---
name: announcements
description: Implementa avisos institucionales de PortalInfor (borrador, publicar, archivar, adjuntos, destinatarios). Usar al construir el módulo de avisos del vicerrectorado/admin o su publicación.
---

# Avisos (Announcements) — PortalInfor

## Nombre

`announcements`

## Propósito

Implementar la gestión de avisos institucionales: crear, editar, publicar, archivar, adjuntar archivos, definir destinatarios/categoría/prioridad y visualizar información de forma clara y responsive para la comunidad educativa.

## Cuándo usarla

- Al desarrollar el módulo de avisos.
- Al implementar flujo borrador → publicado → archivado.
- Al conectar formularios del VICERRECTOR con adjuntos y notificaciones.
- Al construir dashboard de publicaciones.
- Al validar permisos `announcements.*`.

## SPEC relacionada

- `spec/10-avisos.md`
- `spec/27-eliminar-aviso.md`
- `spec/11-notificaciones.md`
- `spec/12-archivos.md`
- `spec/15-perfil-vicerrector.md`
- `spec/06-ui-ux.md`

## Pasos de implementación

1. Leer `spec/10-avisos.md`.
2. Migración `announcements` (+ pivotes destinatarios si aplica).
3. Campos sugeridos: título, descripción, contenido, categoría, prioridad, `published_at`, `expires_at`, estado, archivo, autor, destinatarios.
4. Estados: `borrador` | `publicado` | `archivado`.
5. CRUD + acciones `publish` / `archive` / **`delete`** (SPEC 27).
6. Validaciones: fechas coherentes, título requerido, contenido según reglas.
7. Al publicar: opcionalmente generar notificaciones a destinatarios (skill `notifications`).
8. Listados: admin/vicerrector ven todos/los propios; docentes ven publicados vigentes.
9. UI Bootstrap: formularios, badges de estado, cards o tablas según pantalla.
10. Permisos: `announcements.view|create|edit|delete|publish`.
11. Audit de create/edit/publish/archive/delete.
12. **Eliminar (SPEC 27):** botón en listado “Avisos institucionales” y en ficha; confirmación; POST `/announcements/{id}/delete`; limpiar adjuntos + notificaciones; audit.

## Convenciones de código

- Modelo/servicio: `Announcement`, `AnnouncementService`.
- Solo usuarios con `publish` cambian a `publicado`.
- Avisos expirados no se muestran en feed docente (regla SPEC).
- Soft archive preferible para ocultar del feed; **delete físico** solo con `announcements.delete` y confirmación (SPEC 27).
- Al borrar: eliminar adjuntos (disco+BD) y notificaciones del aviso.
- Slugs/categorías controladas (catálogo).
- Relación 0..1 o 1..n con archivos vía `file-upload`.
- Respuestas flash y AJAX en español.

## Checklist de seguridad

- [ ] Authz en cada acción (incl. publish).
- [ ] CSRF en formularios y AJAX.
- [ ] XSS: escape en título/contenido (o HTML sanitizado si SPEC permite rich text).
- [ ] IDOR: no editar avisos ajenos salvo permiso global.
- [ ] Adjuntos validados.
- [ ] No publicar vía manipulación de campo `status` sin permiso publish.
- [ ] Audit de publicación.

## Checklist de pruebas

- [ ] Crear borrador como VICERRECTOR.
- [ ] Publicar requiere permiso; DOCENTE no publica.
- [ ] Feed docente solo muestra publicados no expirados.
- [ ] Archivar oculta del feed.
- [ ] Editar respeta ownership/permisos.
- [ ] Eliminar desde listado y ficha con confirmación (SPEC 27).
- [ ] DOCENTE no ve ni puede ejecutar delete.
- [ ] Adjuntos y notificaciones se limpian al eliminar.
- [ ] Adjunto PDF se asocia y descarga con permiso.
- [ ] Fechas inválidas (expira < publica) se rechazan.
- [ ] Publicar dispara notificación si SPEC lo exige.

## Errores comunes a evitar

- Usar un solo flag booleano en vez de estados claros.
- Permitir `status=publicado` desde mass assignment.
- Mostrar borradores a docentes.
- Olvidar fecha de expiración en consultas del feed.
- Duplicar lógica de avisos dentro de notificaciones sin contrato.
- UI sobrecargada (romper identidad visual institucional).

## Criterio de done

Avisos está **done** cuando el ciclo borrador/publicado/archivado funciona con permisos correctos, el feed respeta vigencia y audiencia, adjuntos y (si aplica) notificaciones están integrados, seguridad anti-XSS/IDOR/CSRF está cubierta, y las pruebas de aceptación de la SPEC pasan.
