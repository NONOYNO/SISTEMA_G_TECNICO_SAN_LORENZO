---
name: owasp
description: Evalúa y mitiga OWASP Top 10 en SISTEMA_G_TECNICO_SAN_LORENZO (A01–A10) sobre PHP MVC MySQL. Usar en revisiones de seguridad, checklists de aceptación o al implementar controles anti-IDOR, inyección, authn y logging.
---

# OWASP Top 10 — SISTEMA_G_TECNICO_SAN_LORENZO

## Nombre

`owasp`

## Propósito

Aplicar y verificar mitigaciones del OWASP Top 10 en el portal institucional PHP/MySQL, con evidencias de control por categoría (A01–A10) antes de aceptar módulos o releases.

## Cuándo usarla

- Al redactar o ejecutar `spec/21-owasp-top10.md`.
- En revisiones de seguridad pre-entrega.
- Al implementar features con riesgo (uploads, auth, admin, AJAX).
- Cuando QA reporta posibles IDOR, XSS o fallos de sesión.
- Como checklist de cierre junto a `testing` y `security`.

## SPEC relacionada

- `spec/21-owasp-top10.md`
- `spec/05-seguridad.md`
- `spec/22-auditoria.md`
- `spec/07-autenticacion.md`
- `spec/09-roles-permisos.md`

## Pasos de implementación

1. Abrir `spec/21-owasp-top10.md` y esta skill.
2. Por cada categoría A01–A10: listar controles del proyecto y evidencia (archivo/ruta/prueba).
3. Implementar gaps con skills especializadas (`authorization`, `authentication`, `file-upload`, etc.).
4. Ejecutar casos de prueba ofensivos **solo** en entorno local autorizado del propio proyecto.
5. Registrar hallazgos y remediaciones en `docs/` o en la SPEC (estado).
6. No cerrar release con hallazgos críticos abiertos (IDOR, SQLi, credenciales en claro).

### Controles mínimos por categoría

**A01 Broken Access Control**
- Middleware authz; anti-IDOR; sin escalada; denegar por defecto.

**A02 Cryptographic Failures**
- `password_hash`; secretos en `.env`; sin datos sensibles en logs/URLs.

**A03 Injection**
- PDO prepared; escape XSS; sin command execution con input.

**A04 Insecure Design**
- RBAC; mínimo privilegio; threat modeling ligero por módulo; SPEC first.

**A05 Security Misconfiguration**
- Errores ocultos en prod; directorios protegidos; configs hardenizadas.

**A06 Vulnerable Components**
- Inventario de Bootstrap/PHP/extensiones; evitar libs abandonadas.

**A07 Identification & Authentication Failures**
- Sesiones seguras; logout; timeout; anti fuerza bruta; hashing.

**A08 Software & Data Integrity Failures**
- Validar uploads e inputs; no deserializar datos no confiables; integridad de migraciones.

**A09 Security Logging & Monitoring Failures**
- `audit_logs`: login, logout, fallos, CRUD sensible, permisos, files, publish.

**A10 SSRF**
- Evitar fetch a URLs libres del usuario; no proxies abiertos; allowlist si hubiera necesidad.

## Convenciones de código

- Toda feature nueva debe mapear riesgos OWASP relevantes en su SPEC (sección Seguridad).
- Preferir denegación por defecto en authz.
- Tests de seguridad viven en `tests/security/` cuando existan.
- Hallazgos se clasifican: Crítico / Alto / Medio / Bajo.
- No “mitigar” solo con ocultar UI.

## Checklist de seguridad

- [ ] A01: pruebas IDOR y rol cruzado.
- [ ] A02: sin secretos en repo; hashes OK.
- [ ] A03: SQLi/XSS smoke tests OK.
- [ ] A04: RBAC documentado.
- [ ] A05: config local vs prod revisada.
- [ ] A06: dependencias listadas.
- [ ] A07: auth flows OK.
- [ ] A08: uploads/integrity OK.
- [ ] A09: auditoría activa.
- [ ] A10: sin SSRF introducido.

## Checklist de pruebas

- [ ] DOCENTE no abre URLs de admin.
- [ ] Cambiar `id` en URL no filtra recursos ajenos.
- [ ] Payload `' OR '1'='1` no autentica ni lista.
- [ ] `<script>alert(1)</script>` se muestra escapado.
- [ ] Sesión regenerada y timeout.
- [ ] Upload PHP disfrazado rechazado.
- [ ] Eventos clave en `audit_logs`.
- [ ] No hay endpoint que solicite URL arbitraria.

## Errores comunes a evitar

- Checklist OWASP “marcado” sin evidencia.
- Confundir autenticación con autorización.
- Logs que no se revisan nunca.
- Excepciones permanentes tipo “bypass admin”.
- Introducir rich HTML sin sanitizer.
- Probar ataques en sistemas ajenos (prohibido).

## Criterio de done

OWASP está **done** cuando A01–A10 tienen controles implementados y evidencias en SPEC/docs, no quedan hallazgos críticos/altos abiertos del alcance, las pruebas de abuso locales pasan, y el módulo/release puede aceptarse desde seguridad.
