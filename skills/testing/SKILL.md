---
name: testing
description: Define pruebas funcionales, de seguridad y de aceptación para PortalInfor (manuales y automatizadas). Usar antes de aprobar un módulo, tras bugs, o al preparar evidencia QA según SPEC.
---

# Pruebas — PortalInfor

## Nombre

`testing`

## Propósito

Asegurar calidad del portal mediante pruebas funcionales, de seguridad básica, de UI responsive y de aceptación alineadas a cada SPEC: planificar, ejecutar, registrar evidencias y no aprobar sin criterios de aceptación cumplidos.

## Cuándo usarla

- Al terminar la implementación de un módulo.
- Antes de marcar SPEC como “implementado/aprobado”.
- Tras correcciones de bugs o hallazgos de seguridad.
- Al preparar demos institucionales.
- Junto a skills `owasp`, `security`, `deployment`.

## SPEC relacionada

- `spec/20-pruebas.md`
- `spec/25-criterios-aceptacion.md`
- SPEC del módulo bajo prueba
- `spec/21-owasp-top10.md`

## Pasos de implementación

1. Leer sección “Casos de prueba” y “Criterios de aceptación” de la SPEC del módulo.
2. Preparar entorno: migraciones + seeders + `credenciales.md`.
3. Escribir plan breve en `tests/` (manual checklist o scripts PHPUnit/Pest si el proyecto los adopta).
4. Ejecutar matriz por rol: ADMIN, RECTOR, VICERRECTOR, DOCENTE.
5. Cubrir caminos felices + validaciones + autorización denegada.
6. Incluir pruebas AJAX (CSRF, 401/403/422).
7. Incluir smoke de seguridad (XSS/SQLi/IDOR básicos en local).
8. Verificar UI responsive mínima (móvil/tablet/desktop).
9. Registrar resultados (pass/fail) y bugs.
10. Solo entonces actualizar estado de SPEC a validado.

### Tipos de prueba

| Tipo | Objetivo |
|------|----------|
| Funcional | CRUD y flujos de negocio |
| Autenticación/autorización | Login y RBAC |
| AJAX | Contrato JSON y errores |
| Seguridad | OWASP smoke |
| UI/UX | Responsive e identidad |
| Regresión | No romper módulos previos |
| Aceptación | Cumplir SPEC 25 / módulo |

## Convenciones de código

- Tests automatizados en `tests/` espejando módulos (`tests/Feature/UsersTest.php`, etc.).
- Nombres descriptivos en español o inglés consistente con el repo.
- No depender de datos manuales no seedeados.
- Fixtures/seeders de prueba aislados cuando sea posible.
- Evidencias de pruebas manuales: checklist marcado en PR/docs.
- Un bug = caso de prueba de regresión cuando se corrige.

## Checklist de seguridad

- [ ] Casos negativos de authz incluidos.
- [ ] No ejecutar ataques fuera del entorno del proyecto.
- [ ] Datos de prueba sin PII real de estudiantes/padres.
- [ ] Reportes de seguridad sin exponer secretos.
- [ ] Verificar que tests no deshabilitan CSRF “para pasar”.

## Checklist de pruebas

- [ ] Entorno seedeado reproducible.
- [ ] Casos de la SPEC ejecutados.
- [ ] 4 roles verificados donde aplique.
- [ ] Errores de validación cubiertos.
- [ ] AJAX cubierto si el módulo lo usa.
- [ ] Responsive smoke OK.
- [ ] Regresión de login y dashboard OK.
- [ ] Criterios de aceptación en verde.

## Errores comunes a evitar

- Probar solo como ADMIN.
- “Compila/carga = funciona”.
- Ignorar criterios de aceptación de la SPEC.
- Tests flaky acoplados a fechas/horas locales sin control.
- Aprobar con fallos “menores” de seguridad.
- No documentar pasos para reproducir bugs.

## Criterio de done

Testing está **done** cuando los casos de la SPEC y los criterios de aceptación están ejecutados y en verde (o con waivers documentados), hay evidencia por rol, se cubrieron authz/AJAX/seguridad básica aplicables, y el módulo puede marcarse como validado sin deuda crítica conocida.
