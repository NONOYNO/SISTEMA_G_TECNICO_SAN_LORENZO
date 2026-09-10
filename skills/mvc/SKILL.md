---
name: mvc
description: Aplica el patrón Model-View-Controller en PortalInfor (controllers delgados, models/repositories, views Bootstrap, services). Usar al crear endpoints, pantallas o reorganizar lógica entre capas.
---

# MVC — PortalInfor

## Nombre

`mvc`

## Propósito

Asegurar que cada funcionalidad del portal se implemente respetando Model–View–Controller: entrada HTTP en controllers, datos en models/repositories, presentación en views, y reglas de negocio en services — sin contaminar capas.

## Cuándo usarla

- Al crear un módulo nuevo (usuarios, avisos, notificaciones, archivos).
- Al mover lógica mal ubicada (SQL en vistas, HTML en controllers).
- Al diseñar rutas y respuestas (HTML vs JSON AJAX).
- En code review de separación de responsabilidades.

## SPEC relacionada

- `spec/01-arquitectura.md`
- `spec/02-requisitos-funcionales.md`
- SPEC del módulo concreto (ej. `spec/08-usuarios.md`, `spec/10-avisos.md`)

## Pasos de implementación

1. Leer SPEC del módulo y skills relacionadas (`php`, `mysql`, `security`).
2. Definir rutas en `app/routes/` (método HTTP + path + middleware + controller@action).
3. Crear/actualizar **Controller**: recibir request, validar CSRF/authz, llamar service, responder.
4. Crear/actualizar **Service**: reglas de negocio, orquestación, auditoría.
5. Crear/actualizar **Repository/Model**: persistencia PDO.
6. Crear/actualizar **Validator**: reglas de campos.
7. Crear **View** o respuesta JSON AJAX según el caso.
8. Registrar middleware (`Auth`, `Permission`, `Guest`) en la ruta.
9. Probar flujo completo y marcar SPEC.

## Convenciones de código

### Controller
- Métodos: `index`, `show`, `create`, `store`, `edit`, `update`, `destroy` (+ acciones de dominio).
- No contiene SQL ni HTML complejo.
- Devuelve view o `jsonResponse()`.

### Model / Repository
- Model: representación de entidad y helpers simples.
- Repository: queries PDO tipadas por operación.
- Nombres: `UserRepository`, `AnnouncementRepository`.

### View
- PHP + Bootstrap 5; layouts en `views/layouts/`.
- Partials reutilizables (`navbar`, `sidebar`, `alerts`).
- Escape de salida obligatorio.
- Sin consultas a BD.

### Service
- Casos de uso: `AuthService`, `AnnouncementService`.
- Transacciones y side-effects (notificaciones, archivos, audit).

### Request flow

```text
HTTP → Router → Middleware → Controller → Service → Repository → MySQL
                              ↓
                         View / JSON
```

## Checklist de seguridad

- [ ] Middleware de auth/authz en rutas sensibles.
- [ ] CSRF en `store`/`update`/`destroy` y AJAX mutadores.
- [ ] Autorización también en service (no solo UI).
- [ ] Views escapan datos de usuario.
- [ ] Controllers no confían en IDs de cliente sin ownership check (anti-IDOR).

## Checklist de pruebas

- [ ] Ruta pública y protegida se comportan distinto.
- [ ] CRUD completo del recurso vía MVC.
- [ ] Vista renderiza sin notices PHP.
- [ ] JSON AJAX mantiene el mismo contrato de capas.
- [ ] Error de validación vuelve a form con mensajes.
- [ ] Eliminación/actualización no autorizada retorna 403.

## Errores comunes a evitar

- “Fat controllers” con negocio + SQL + HTML.
- Llamar PDO desde la vista.
- Duplicar reglas de permiso solo en botones Bootstrap.
- Responder HTML desde endpoints pensados para AJAX (o viceversa) sin contrato claro.
- Models anémicos + SQL disperso en helpers globales.
- Saltar SPEC/SKILL e improvisar carpetas.

## Criterio de done

MVC está **done** cuando el módulo tiene ruta, controller, service/repository, validator y view/JSON alineados a la SPEC; la lógica de negocio no está en la vista; las pruebas del flujo HTTP pasan; y la autorización se aplica en backend.
