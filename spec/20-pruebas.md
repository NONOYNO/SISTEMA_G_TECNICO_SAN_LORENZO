# SPEC 20 — Pruebas del sistema

**Proyecto:** Unidad Educativa Fiscomisional San Lorenzo  
**Stack:** PHP 8 + MySQL + PDO + MVC + Bootstrap 5 + AJAX + XAMPP  
**Módulo:** Estrategia de pruebas (unitarias, integración, seguridad, funcionales)  
**Estado:** PENDING

---

## 1. Objetivo

Establecer el plan de pruebas del portal para validar correctitud funcional, integración entre módulos, seguridad (OWASP) y aceptación por rol, con matriz trazable ID → resultado.

## 2. Alcance

**Incluye:**
- Carpeta `/tests` (unit, integration, security, functional).
- Convenciones de nombrado de casos.
- Matriz de pruebas.
- Criterios de ejecución en XAMPP / CLI PHPUnit (o runner simple PHP).
- Datos de prueba vía seeders.

**Excluye:**
- Pruebas de carga masiva / pentest externo formal (se documentan checks básicos).
- Pruebas de UI visual pixel-perfect.

## 3. Actores

| Actor | Responsabilidad |
|-------|-----------------|
| Desarrollador | Unitarias + integración al implementar |
| QA / Agente | Matriz funcional y seguridad |
| ADMIN local | Apoyo con seeders y ambiente |

## 4. Requisitos

### Tipos de prueba
- RF-T01 **Unitarias:** validadores, hash password, policies de permisos, helpers, servicios puros.
- RF-T02 **Integración:** login, CRUD usuarios, avisos, notificaciones, archivos, migraciones.
- RF-T03 **Seguridad:** SQLi, XSS, CSRF, IDOR, escalada, auth bypass, upload malicioso, path traversal.
- RF-T04 **Funcionales:** flujos por rol ADMIN/RECTOR/VICERRECTOR/DOCENTE.
- RF-T05: Cada caso tiene ID único (`AUTH-001`, `ANN-010`, `SEC-003`…).
- RF-T06: Resultados: PASS / FAIL / BLOCKED / SKIP.
- RF-T07: Ambiente de test usa DB `ue_san_lorenzo_test` cuando sea posible.

## 5. Flujo funcional

```text
1. Preparar entorno (migrate + seed test)
2. Ejecutar unitarias
3. Ejecutar integración
4. Ejecutar seguridad
5. Ejecutar funcionales por rol
6. Actualizar matriz (Resultado obtenido / Estado)
7. Fallos → bug → fix → retest
```

## 6. Reglas de negocio

- RN-T01: No se aprueba módulo sin SPEC + SKILL + pruebas mínimas del módulo.
- RN-T02: Fallo de seguridad es bloqueante (no se entrega).
- RN-T03: Credenciales solo las de seeder.
- RN-T04: Tests no deben depender de datos manuales no versionados.
- RN-T05: Limpiar uploads de test después de la suite.
- RN-T06: Cobertura mínima orientativa: validadores y authz > 80% de rutas críticas listadas.

## 7. Estructura de datos

### Matriz (campos)
| Campo | Descripción |
|-------|-------------|
| ID | Identificador único |
| Funcionalidad | Módulo/caso |
| Precondición | Estado inicial |
| Entrada | Datos / pasos |
| Resultado esperado | Comportamiento correcto |
| Resultado obtenido | Qué ocurrió |
| Estado | PASS/FAIL/BLOCKED/SKIP |
| Observaciones | Notas |

### Estructura de carpetas
```text
tests/
├── unit/
├── integration/
├── security/
├── functional/
└── matrices/
    └── matriz-pruebas.md
```

## 8. Validaciones

- IDs únicos en matriz.
- Precondiciones verificables (usuario activo, permiso X).
- Assertions explícitas (status HTTP, JSON `success`, filas DB).
- Casos negativos incluidos (403, 422, 401).

## 9. Seguridad

- DB de test aislada.
- No apuntar tests destructivos a producción.
- Payloads de seguridad solo en entorno local.
- No commitear reports con secretos.
- Cumplir `spec/21-owasp-top10.md` como checklist de suite SEC-*.

## 10. Interfaz

- No UI obligatoria; reporte Markdown/HTML opcional.
- PHPUnit text runner o script `php tests/run.php`.
- Matriz editable en `tests/matrices/matriz-pruebas.md`.

## 11. AJAX requerido

Las pruebas de integración/funcionales **deben cubrir** endpoints AJAX críticos:

- Login session + `/ajax/dashboard/metrics`
- `/ajax/notifications/mark-read`
- `/ajax/announcements` (CRUD VICERRECTOR)
- `/ajax/admin/users`
- Upload `/ajax/attachments/upload`

No se requiere AJAX para ejecutar el runner de tests.

## 12. Respuestas esperadas

**Suite OK:**
```text
OK (128 tests, 0 failures)
```

**Ejemplo fila matriz:**
```text
AUTH-001 | Login válido | Usuario activo | admin@uesanlorenzo.edu / Admin123! | Acceso dashboard ADMIN | Acceso OK | PASS |
```

## 13. Manejo de errores

- Fallo de setup (migrate) → abortar suite, estado BLOCKED.
- Flaky tests → documentar y corregir causas (sesión, orden).
- Assert failure → mensaje con ID de caso.
- Cleanup en `finally` / tearDown.

## 14. Casos de prueba (matriz base)

### Autenticación
| ID | Funcionalidad | Precondición | Entrada | Resultado esperado |
|----|---------------|--------------|---------|-------------------|
| AUTH-001 | Login válido | Seed users | admin@uesanlorenzo.edu / Admin123! | Dashboard ADMIN |
| AUTH-002 | Login inválido | Seed | password mala | Error, sin sesión |
| AUTH-003 | Logout | Sesión activa | logout | Sesión destruida |
| AUTH-004 | Timeout | Sesión vieja | request | 401/redirect |

### Autorización
| ID | Funcionalidad | Precondición | Entrada | Resultado esperado |
|----|---------------|--------------|---------|-------------------|
| AUTZ-001 | DOCENTE → admin users | Login docente | GET /admin/users | 403 |
| AUTZ-002 | VICERRECTOR publish | Login vic. | POST publish | 200 |
| AUTZ-003 | RECTOR publish | Login rector | POST publish | 403 |
| AUTZ-004 | IDOR descarga | Login docente | file_id ajeno | 403 |

### Avisos / Notificaciones / Archivos
| ID | Funcionalidad | Precondición | Entrada | Resultado esperado |
|----|---------------|--------------|---------|-------------------|
| ANN-001 | Crear borrador | VICERRECTOR | form válido | draft |
| ANN-002 | Publicar | borrador | publish | published + notify |
| NOT-001 | Mark read | DOCENTE | notification_id | unread-- |
| FIL-001 | Upload PDF | VICERRECTOR | file.pdf | OK |
| FIL-002 | Upload .php | VICERRECTOR | shell.php | Rechazo |

### Seguridad
| ID | Funcionalidad | Precondición | Entrada | Resultado esperado |
|----|---------------|--------------|---------|-------------------|
| SEC-001 | SQLi login | — | `' OR 1=1--` | Fallo login, sin bypass |
| SEC-002 | XSS aviso | VICERRECTOR | `<script>` en título | Escapado |
| SEC-003 | CSRF | sesión | POST sin token | Rechazo |
| SEC-004 | Path traversal | sesión | `../../etc/passwd` | 400/403 |
| SEC-005 | Privilege escalate | DOCENTE | role=ADMIN POST | Ignorado/403 |

### Migraciones / Seeders
| ID | Funcionalidad | Resultado esperado |
|----|---------------|-------------------|
| MIG-T01 | migrate fresh | esquema OK |
| SEED-T01 | seed users | 4 logins OK |

## 15. Criterios de aceptación

- [ ] Existen suites unit/integration/security/functional.
- [ ] Matriz documentada y actualizable.
- [ ] Casos AUTH, AUTZ, ANN, NOT, FIL, SEC mínimos PASS.
- [ ] Fallos de seguridad bloquean entrega.
- [ ] Seeders permiten reproducir pruebas.
- [ ] Runner documentado en README/tests.

## 16. Dependencias

- Specs 07–19, 21, 22, 25
- Skills: `testing`, `security`, `owasp`, `seeders`

## 17. SKILL requerida

```text
skills/testing/SKILL.md
```

## 18. Estado de implementación

**PENDING**
