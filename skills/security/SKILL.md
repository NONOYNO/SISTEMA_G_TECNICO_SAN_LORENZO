---
name: security
description: Aplica controles de seguridad transversales en SISTEMA_G_TECNICO_SAN_LORENZO (sesiones, CSRF, validación, headers, secretos, auditoría). Usar al endurecer el sistema, revisar configs XAMPP o antes de dar por cerrada una feature.
---

# Seguridad — SISTEMA_G_TECNICO_SAN_LORENZO

## Nombre

`security`

## Propósito

Establecer la línea base de seguridad del portal: configuración segura, protección de datos, sesiones, CSRF, validación/salida, cabeceras, secretos, logging/auditoría y endurecimiento en XAMPP/Apache — complementando OWASP Top 10 y skills de authz/authn.

## Cuándo usarla

- Al configurar el proyecto por primera vez.
- Antes de marcar un módulo como completo.
- Al revisar `.env`, Apache, permisos de carpetas.
- Tras encontrar un hallazgo de seguridad.
- Junto a despliegue local/producción controlada.

## SPEC relacionada

- `spec/05-seguridad.md`
- `spec/21-owasp-top10.md`
- `spec/22-auditoria.md`
- `spec/07-autenticacion.md`
- `spec/29` CSRF (si está en master) / sección CSRF del prompt master
- `spec/24-despliegue-xampp.md`

## Pasos de implementación

1. Leer `spec/05-seguridad.md`.
2. Configurar `.env.example` sin secretos reales; `.env` en `.gitignore`.
3. Endurecer sesión PHP (nombre, params cookie, regeneración, timeout).
4. Implementar tokens CSRF globales para forms y AJAX.
5. Forzar validación server-side en todos los inputs.
6. Escape de salida en views; política clara si hay HTML enriquecido.
7. PDO prepared statements únicamente.
8. Middleware auth/authz en rutas protegidas.
9. Cabeceras: `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, CSP básica si aplica.
10. Restringir acceso a `storage/`, `logs/`, `uploads/` (sin listar ni ejecutar).
11. Implementar `audit_logs` para eventos sensibles.
12. Desactivar `display_errors` fuera de local; loguear a `logs/`.
13. Revisar permisos de archivos en Windows/XAMPP.

## Convenciones de código

- Helper `e($value)` → `htmlspecialchars($value, ENT_QUOTES, 'UTF-8')`.
- Helper CSRF: `csrf_token()`, `csrf_field()`, `verify_csrf()`.
- Errores al cliente: mensajes genéricos; detalle solo en log.
- Principio de mínimo privilegio en roles y en usuario MySQL.
- No commitear `credenciales.md` con secretos de producción (solo pruebas).
- Dependencias frontend/backend versionadas y documentadas.
- Separar perfiles: local vs producción en config.

## Checklist de seguridad

- [ ] Secretos fuera del repo.
- [ ] CSRF activo.
- [ ] Sesiones seguras.
- [ ] Hash de passwords.
- [ ] Authz backend.
- [ ] PDO prepared.
- [ ] Escape XSS.
- [ ] Uploads endurecidos.
- [ ] Headers básicos.
- [ ] Auditoría de eventos clave.
- [ ] Directorios sensibles protegidos.
- [ ] Errores no verbosos en prod.

## Checklist de pruebas

- [ ] Form sin CSRF falla.
- [ ] XSS reflejado básico no ejecuta script.
- [ ] SQL con comillas en input no rompe query.
- [ ] Usuario sin rol no entra a admin.
- [ ] `.env` no es servido por Apache.
- [ ] `uploads/` no ejecuta PHP.
- [ ] Login fallido y OK quedan en audit/log.
- [ ] Sesión expira tras timeout configurado.

## Errores comunes a evitar

- “Ya está seguro porque está en intranet”.
- Dejar `phpinfo.php` o installers.
- Misma clave de APP_KEY/CSRF débil o fija en repo.
- Logs con contraseñas o tokens completos.
- Confiar en validación solo JS.
- CORS abierto innecesario.
- Ignorar actualizaciones de Bootstrap/jQuery vulnerables.

## Criterio de done

Seguridad transversal está **done** cuando la SPEC `05` está cubierta: secretos, sesiones, CSRF, validación, escape, PDO, authz, headers, uploads y auditoría operan; las pruebas de abuso básicas fallan a favor del sistema; y no quedan artefactos de debug expuestos.
