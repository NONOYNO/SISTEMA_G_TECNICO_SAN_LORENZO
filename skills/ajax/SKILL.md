---
name: ajax
description: Define contratos AJAX/Fetch seguros para SISTEMA_G_TECNICO_SAN_LORENZO (JSON, CSRF, authz, errores). Usar al implementar búsquedas, filtros, mark-as-read, paginación dinámica o cualquier mutación sin recarga.
---

# AJAX — SISTEMA_G_TECNICO_SAN_LORENZO

## Nombre

`ajax`

## Propósito

Estandarizar peticiones asíncronas (AJAX / Fetch API) para mejorar UX sin debilitar seguridad: autenticación, autorización, CSRF, validación, manejo de errores y contrato JSON uniforme en el portal.

## Cuándo usarla

- Búsqueda y filtros de usuarios.
- Marcar notificaciones como leídas.
- Cambiar estados (activar usuario, archivar aviso).
- Paginación o carga dinámica de cards/tablas.
- Validaciones remotas (username/email disponible).
- Cualquier POST/PUT/DELETE sin recarga completa.

## SPEC relacionada

- SPEC del módulo que usa AJAX (`08`, `10`, `11`, etc.)
- `spec/05-seguridad.md`
- `spec/07-autenticacion.md`
- `spec/09-roles-permisos.md`
- `spec/06-ui-ux.md`

## Pasos de implementación

1. Confirmar en SPEC que la operación requiere AJAX.
2. Crear endpoint en `app/ajax/` o acción de controller que detecte `Accept: application/json` / header `X-Requested-With`.
3. Validar sesión autenticada (si aplica).
4. Validar permiso de autorización.
5. Validar token CSRF (header `X-CSRF-TOKEN` o campo).
6. Validar y sanitizar payload.
7. Ejecutar service; capturar errores de dominio.
8. Responder JSON con código HTTP adecuado.
9. En frontend: `fetch` con credentials, CSRF, manejo de `success/errors`.
10. Actualizar UI (DOM) sin romper accesibilidad ni layout Bootstrap.
11. Registrar audit si la acción es sensible.

## Convenciones de código

### Contrato JSON de respuesta

```json
{
  "success": true,
  "message": "Operación realizada correctamente",
  "data": {},
  "errors": {}
}
```

- `success: false` + `errors` por campo en validación (HTTP 422).
- 401 no autenticado; 403 no autorizado; 404 no encontrado; 500 genérico.
- JS en `app/assets/js/` modular por feature (`users.js`, `notifications.js`).
- Preferir Fetch API; jQuery solo si ya está adoptado en el proyecto.
- Siempre enviar CSRF en mutaciones.
- No asumir que “viene de nuestro JS” = seguro.
- Timeouts y mensajes de red en español.
- Deshabilitar botón submit durante request para evitar dobles envíos.

## Checklist de seguridad

- [ ] Auth + authz en servidor.
- [ ] CSRF en POST/PUT/PATCH/DELETE.
- [ ] Validación backend completa.
- [ ] Sin datos sensibles extra en `data`.
- [ ] Rate limit básico en endpoints abusables (login, search).
- [ ] Escape al insertar HTML desde JSON en el DOM (`textContent` o sanitizar).
- [ ] Same-origin / no JSONP.

## Checklist de pruebas

- [ ] Request válida OK y UI se actualiza.
- [ ] Sin CSRF → rechazo.
- [ ] Sin sesión → 401.
- [ ] Sin permiso → 403.
- [ ] Payload inválido → 422 con errores.
- [ ] Doble clic no duplica registros.
- [ ] Falla de red muestra mensaje usable.
- [ ] Funciona en desktop y mobile.

## Errores comunes a evitar

- Endpoints AJAX sin las mismas reglas que el formulario clásico.
- Devolver HTML de error PHP en vez de JSON.
- Insertar `innerHTML` con datos de usuario sin sanitizar.
- Olvidar `credentials: 'same-origin'` / cookies de sesión.
- Contratos JSON distintos por módulo.
- Usar GET para borrar o cambiar estado.

## Criterio de done

AJAX está **done** cuando el endpoint cumple el contrato JSON, aplica auth/authz/CSRF/validación igual que el flujo no AJAX, la UI responde correctamente a éxito y error, no introduce XSS vía DOM, y las pruebas manuales/automatizadas del caso pasan.
