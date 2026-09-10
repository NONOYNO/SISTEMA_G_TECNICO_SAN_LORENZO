---
name: authorization
description: Aplica control de acceso RBAC en backend para SISTEMA_G_TECNICO_SAN_LORENZO (permisos granulares, middleware, anti-IDOR). Usar al proteger rutas, acciones AJAX, menús o validar que un rol no escale privilegios.
---

# Autorización — SISTEMA_G_TECNICO_SAN_LORENZO

## Nombre

`authorization`

## Propósito

Garantizar que cada acción del sistema se autorice en el servidor según roles y permisos (RBAC). La UI puede ocultar botones, pero **nunca** es la única defensa. Cubre middleware, checks en services y prevención de IDOR/escalada.

## Cuándo usarla

- Al proteger una ruta, acción o endpoint AJAX.
- Al asignar o evaluar permisos (`users.view`, `announcements.publish`, etc.).
- Al implementar perfiles ADMIN / RECTOR / VICERRECTOR / DOCENTE.
- Al auditar Broken Access Control (OWASP A01).
- Cuando un usuario reporta “veo/hago algo que no debería”.

## SPEC relacionada

- `spec/09-roles-permisos.md`
- `spec/05-seguridad.md`
- `spec/21-owasp-top10.md`
- SPEC de perfiles: `spec/14`–`spec/17` (docente, vicerrector, admin, rector)

## Pasos de implementación

1. Leer SPEC de roles/permisos y del módulo.
2. Confirmar permisos granulares en BD (`permissions`, `role_permissions`, `user_roles`).
3. Implementar `AuthorizationService` / helper `can($user, $permission)`.
4. Crear middleware `Permission:announcements.publish` (o equivalente).
5. Aplicar middleware en rutas mutadoras y de lectura sensibles.
6. En services, revalidar permiso + ownership (ej. solo editar propios borradores si aplica).
7. Para recursos por ID: verificar que el usuario puede acceder a *ese* registro (anti-IDOR).
8. Denegar con 403 (HTML o JSON `{ success:false }`).
9. Registrar en `audit_logs` intentos denegados relevantes.
10. Ajustar menús/sidebar según permisos (UX), sin relajar backend.

## Convenciones de código

- Permisos en notación `recurso.accion` en minúsculas.
- Roles canónicos: `ADMIN`, `RECTOR`, `VICERRECTOR`, `DOCENTE`.
- ADMIN puede tener bypass explícito documentado o conjunto completo de permisos vía seeder — nunca “if role == admin” disperso sin criterio.
- Preferir chequear permiso, no solo nombre de rol, salvo reglas de negocio de perfil.
- Centralizar checks; no copiar ifs en cada vista.
- Respuesta denegada consistente en web y AJAX.

## Checklist de seguridad

- [ ] Toda ruta sensible tiene auth + authz.
- [ ] Endpoints AJAX validan permiso igual que formularios.
- [ ] No hay IDOR (acceso por ID ajeno).
- [ ] Escalada de privilegios bloqueada (autoasignarse ADMIN).
- [ ] Permisos no se aceptan desde el cliente como fuente de verdad.
- [ ] Principio de mínimo privilegio en roles por defecto.
- [ ] Audit de cambios de roles/permisos.

## Checklist de pruebas

- [ ] DOCENTE no accede a gestión de usuarios/roles.
- [ ] VICERRECTOR puede crear/publicar avisos según SPEC; otros no.
- [ ] RECTOR solo consulta lo autorizado.
- [ ] ADMIN accede a administración.
- [ ] Petición directa a URL prohibida → 403/redirect.
- [ ] AJAX sin permiso → 403 JSON.
- [ ] Usuario A no edita recurso de usuario B si no corresponde.
- [ ] Quitar permiso en BD revoca acceso sin redeploy de UI.

## Errores comunes a evitar

- Ocultar botón y creer que ya está seguro.
- Autorizar solo por rol hardcodeado en vistas.
- Confiar en parámetros ocultos (`role=ADMIN` en POST).
- Olvidar authz en un endpoint AJAX “interno”.
- Permisos demasiado amplios en seeders de “comodidad”.
- Mezclar autenticación (¿quién eres?) con autorización (¿qué puedes?).

## Criterio de done

Autorización está **done** cuando cada acción sensible valida permiso en backend, los cuatro roles se comportan según SPEC, IDOR/escalada están cubiertos por pruebas, AJAX y formularios comparten la misma política, y los denegados responden 403 de forma consistente.
