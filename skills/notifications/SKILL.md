---
name: notifications
description: Implementa notificaciones institucionales de PortalInfor (crear, listar en cards, marcar leídas, destinatarios, AJAX). Usar al construir el módulo de notificaciones o su integración con avisos y archivos.
---

# Notificaciones — PortalInfor

## Nombre

`notifications`

## Propósito

Gestionar notificaciones del portal institucional: creación (principalmente VICERRECTOR/ADMIN), destinos por rol/usuario, visualización en cards, estado de lectura, prioridades y vínculo opcional a archivos o avisos.

## Cuándo usarla

- Al implementar el módulo de notificaciones.
- Al mostrar campana/contador o listado en cards.
- Al marcar como leída vía AJAX.
- Al integrar destinatarios con roles/usuarios.
- Al probar flujos DOCENTE (consulta) vs VICERRECTOR (creación).

## SPEC relacionada

- `spec/11-notificaciones.md`
- `spec/10-avisos.md`
- `spec/12-archivos.md`
- `spec/09-roles-permisos.md`
- `spec/06-ui-ux.md`

## Pasos de implementación

1. Leer `spec/11-notificaciones.md` (campos, estados, AJAX, UI cards).
2. Migraciones: `notifications`, `notification_user` (pivote lectura/destinatario) u equivalente.
3. Campos típicos: título, mensaje/contenido, prioridad, categoría, fechas, estado, autor, archivo opcional, destinatarios.
4. `NotificationController` + `NotificationService` + repository.
5. Crear notificación con validación de permiso `notifications.create`.
6. Listado para destinatarios con cards Bootstrap (prioridad, fecha, autor, badge no leída).
7. Endpoint AJAX `markAsRead` / `markAllRead` con CSRF + authz.
8. Contador de no leídas en navbar.
9. Filtrar por prioridad/categoría/fecha según SPEC.
10. Audit de creación y (si aplica) eliminación.
11. Integrar descarga de adjunto con skill `file-upload`.

## Convenciones de código

- Permisos: `notifications.view`, `notifications.create` (+ delete si SPEC).
- Prioridades: `baja` | `normal` | `alta` | `urgente` (o las de SPEC).
- UI: cards, no tablas densas para el feed del docente.
- JSON AJAX: `{ success, message, data: { unread_count } }`.
- No filtrar notificaciones de otros destinatarios (anti-IDOR en mark read).
- Textos en español institucional.
- Relación clara con avisos si la SPEC unifica “Avisos y Notificaciones”.

## Checklist de seguridad

- [ ] Solo roles autorizados crean notificaciones.
- [ ] Destinatarios no se manipulan para spam masivo sin permiso.
- [ ] CSRF en create y mark-read.
- [ ] Usuario solo marca/lee las suyas.
- [ ] Escape XSS en título/contenido renderizado.
- [ ] Adjuntos pasan validación de `file-upload`.
- [ ] Audit de creación.

## Checklist de pruebas

- [ ] VICERRECTOR crea notificación para DOCENTE.
- [ ] DOCENTE la ve en cards; RECTOR según permisos.
- [ ] Marcar leída reduce contador (AJAX).
- [ ] Usuario B no marca leída la de A.
- [ ] Prioridad alta se destaca visualmente.
- [ ] Sin permiso create → 403.
- [ ] Contenido con HTML malicioso se escapa.
- [ ] Paginación/filtros del feed funcionan.

## Errores comunes a evitar

- Tratar notificaciones como anuncios públicos sin destinatarios.
- Marcar leídas solo en localStorage sin persistir en BD.
- Olvidar authz en endpoint AJAX de lectura.
- Mezclar por completo avisos y notificaciones sin modelo claro en SPEC.
- Cards sin estados vacíos (“No hay notificaciones”).
- Enviar notificaciones a usuarios inactivos sin regla definida.

## Criterio de done

Notificaciones está **done** cuando creación, destinos, listado en cards, lectura AJAX y permisos cumplen la SPEC; no hay IDOR de lectura; XSS está controlado; y las pruebas por rol (crear vs solo ver) pasan.
