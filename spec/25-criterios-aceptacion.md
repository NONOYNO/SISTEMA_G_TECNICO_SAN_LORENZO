# SPEC 25 — Criterios de aceptación finales

**Proyecto:** Unidad Educativa Fiscomisional San Lorenzo  
**Stack:** PHP 8 + MySQL + PDO + MVC + Bootstrap 5 + AJAX + XAMPP  
**Módulo:** Checklist de entrega del sistema completo  
**Estado:** PENDING

---

## 1. Objetivo

Definir el checklist final de aceptación para considerar el portal **entregable**: especificación, skills, código, seguridad, pruebas, datos semilla, despliegue XAMPP y documentación alineados a la metodología SPEC → SKILL → CODE.

## 2. Alcance

**Incluye:**
- Criterios globales de producto.
- Criterios por módulo/rol.
- Criterios de seguridad OWASP.
- Criterios de calidad (MVC, PDO, UI).
- Criterios de documentación y reproducibilidad.
- Definición de “DONE” vs “PENDING”.

**Excluye:**
- Aceptación de cambios de alcance no especificados.
- Certificaciones legales externas.

## 3. Actores

| Actor | Responsabilidad |
|-------|-----------------|
| Product Owner / Dirección | Firma aceptación funcional |
| Desarrollador / Agente IA | Completa checklist técnico |
| QA | Ejecuta matriz `spec/20` |
| ADMIN | Valida operación en XAMPP |

## 4. Requisitos

El sistema se acepta solo si cumple **todas** las secciones 15 (checklist) de este documento y no quedan ítems bloqueantes FAIL en seguridad.

## 5. Flujo funcional de aceptación

```text
1. Verificar SPECs 00–25 existen y coherentes
2. Verificar SKILLS requeridas existen
3. Verificar código MVC + PDO + Bootstrap 5 + AJAX
4. Desplegar en XAMPP (spec/24)
5. Migrar + seed (18, 19)
6. Ejecutar matriz de pruebas (20)
7. Revisar OWASP (21) + auditoría (22)
8. Probar backup (23)
9. Recorrido UAT por rol (13–17)
10. Marcar módulos DONE / registrar pendientes
11. Acta de entrega / CHANGELOG
```

## 6. Reglas de negocio

- RN-C01: Sin SPEC ni SKILL no hay aceptación del módulo.
- RN-C02: Cualquier FAIL de seguridad (SEC/OWASP) bloquea entrega.
- RN-C03: Credenciales de entrega = seeders de prueba, no secretos reales.
- RN-C04: UI debe ser responsive y con identidad celeste/azul/blanco.
- RN-C05: Todos los endpoints sensibles con authz backend + CSRF.
- RN-C06: Estado de cada SPEC actualizable de PENDING → DONE solo tras evidencia de pruebas.

## 7. Estructura de datos (evidencias)

Entregables mínimos:

```text
spec/*.md
skills/**/SKILL.md
app/ (MVC)
database/migrations/
database/seeders/
tests/ + matrices/
docs/ (README, CHANGELOG)
credenciales.md (dev)
.env.example
storage/ uploads/ logs/ (con .gitkeep)
```

## 8. Validaciones

- Checklist marcado con evidencia (ID de prueba o captura/log).
- Versión PHP 8+ confirmada.
- DB utf8mb4.
- 4 logins seed OK.
- No existen rutas debug expuestas con `APP_DEBUG=false`.

## 9. Seguridad

Obligatorio para aceptar:
- PDO prepared statements en todas las queries con input.
- RBAC enforced server-side.
- Uploads endurecidos.
- audit_logs operativo.
- Carpetas sensibles no públicas.
- OWASP A01–A10 mitigados o N/A justificado.

## 10. Interfaz

Criterios UX/UI de aceptación:
- [ ] Login institucional claro.
- [ ] Dashboards distintos por rol.
- [ ] Cards de avisos/notificaciones.
- [ ] Tablas admin responsive.
- [ ] Mensajes de error/éxito comprensibles.
- [ ] Uso correcto en mobile/tablet/desktop.

## 11. AJAX requerido

Verificar en UAT que funcionan al menos:
- [ ] Métricas dashboard
- [ ] Marcar notificación leída
- [ ] Filtros/listados admin y avisos
- [ ] Upload adjunto (VICERRECTOR)
- [ ] Respuestas JSON uniformes `{success, message, data}`

## 12. Respuestas esperadas (definición de Done)

El sistema está **ACEPTADO** cuando:
1. Checklist sección 15 = 100% de ítems obligatorios en PASS.
2. Matriz crítica AUTH/AUTZ/ANN/NOT/FIL/SEC en PASS.
3. Despliegue XAMPP reproducible por un tercero con este SPEC + README.
4. No hay secretos de producción en el repositorio.

## 13. Manejo de errores / no conformidad

| Severidad | Ejemplo | Acción |
|-----------|---------|--------|
| Bloqueante | SQLi, bypass authz | Rechazar entrega |
| Mayor | CRUD roto, seed falla | Corregir antes de aceptar |
| Menor | Texto UI, estilo | Aceptar condicionado con plazo |
| Cosmético | Icono | Backlog |

## 14. Casos de prueba (UAT final por rol)

| ID | Rol | Recorrido | Esperado |
|----|-----|-----------|----------|
| UAT-01 | ADMIN | Login → usuarios → roles → auditoría → reporte | OK total |
| UAT-02 | VICERRECTOR | Crear→publicar aviso + adjunto + notificación | Destinatarios ven info |
| UAT-03 | RECTOR | Consultar avisos/reportes; denegado admin | 403 en admin |
| UAT-04 | DOCENTE | Ver avisos, marcar leídas, descargar, editar perfil permitido | OK |
| UAT-05 | Anónimo | Rutas privadas | Redirect login |
| UAT-06 | Técnico | migrate/seed/backup en XAMPP | Reproducible |

## 15. Criterios de aceptación (checklist final)

### A. Metodología y documentación
- [ ] Existen SPECs del sistema (incl. 13–25).
- [ ] Existen SKILLS referenciadas por cada módulo crítico.
- [ ] README con instalación XAMPP.
- [ ] CHANGELOG actualizado.
- [ ] `.env.example` completo.
- [ ] `credenciales.md` de prueba presente (dev).

### B. Arquitectura y stack
- [ ] PHP 8 + MVC + PDO + MySQL.
- [ ] Bootstrap 5 + AJAX en operaciones clave.
- [ ] Estructura de carpetas según prompt master.
- [ ] Separación config/controllers/models/views.

### C. Autenticación y RBAC
- [ ] Login/logout/sesión segura.
- [ ] Roles ADMIN, RECTOR, VICERRECTOR, DOCENTE.
- [ ] Permisos granulares enforced en backend.
- [ ] Seed users `@uesanlorenzo.edu` operativos.

### D. Módulos funcionales
- [ ] Dashboard por rol (`spec/13`).
- [ ] Perfil DOCENTE (`spec/14`).
- [ ] Perfil VICERRECTOR publicaciones (`spec/15`).
- [ ] Perfil ADMIN gestión total (`spec/16`).
- [ ] Perfil RECTOR consulta (`spec/17`).
- [ ] Avisos, notificaciones, archivos operativos.

### E. Datos y operaciones
- [ ] Migraciones naming `YYYYMMDDHHMMSS_descripcion.php` (`spec/18`).
- [ ] Seeders roles/permisos/users (`spec/19`).
- [ ] Auditoría `audit_logs` (`spec/22`).
- [ ] Backup DB + storage (`spec/23`).
- [ ] Despliegue XAMPP verificado (`spec/24`).

### F. Calidad y seguridad
- [ ] Matriz de pruebas ejecutada (`spec/20`).
- [ ] OWASP Top 10 mitigado (`spec/21`).
- [ ] Sin FAIL bloqueantes de seguridad.
- [ ] CSRF, XSS escape, SQLi prevenido.
- [ ] Uploads no ejecutables.

### G. Entrega
- [ ] UAT-01…UAT-06 PASS.
- [ ] Pendientes menores documentados (si existen).
- [ ] Estados SPEC actualizados (PENDING→DONE) con evidencia.
- [ ] Firma/aceptación del responsable del proyecto.

## 16. Dependencias

- Todas las SPEC `00`–`24` (o el conjunto implementado del proyecto).
- Skills: `testing`, `deployment`, `owasp`, `security`, perfiles y módulos.
- Ambiente: XAMPP en `C:\xampp\htdocs\SISTEMA_G_TECNICO_SAN_LORENZO`.

## 17. SKILL requerida

```text
skills/acceptance/SKILL.md
```

(Complementarias: `skills/testing/SKILL.md`, `skills/deployment/SKILL.md`)

## 18. Estado de implementación

**PENDING**

---

### Nota de cierre

Este documento es la **puerta de calidad** del proyecto. Ningún módulo se considera cerrado mientras su SPEC figure en PENDING sin evidencia de implementación + pruebas asociadas.
