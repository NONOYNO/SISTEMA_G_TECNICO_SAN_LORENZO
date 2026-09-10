---
name: architecture
description: Define y aplica la arquitectura MVC de PortalInfor (Unidad Educativa Fiscomisional San Lorenzo) sobre PHP 8, MySQL PDO, Bootstrap 5, AJAX y XAMPP. Usar al iniciar el proyecto, crear módulos nuevos, reorganizar carpetas o validar que el diseño respete SPEC → SKILL → CODE.
---

# Arquitectura — PortalInfor

## Nombre

`architecture`

## Propósito

Establecer la estructura técnica, capas, dependencias y límites del sistema informativo-administrativo de la Unidad Educativa Fiscomisional San Lorenzo. Garantiza que cada módulo se construya sobre una arquitectura MVC clara, segura y mantenible, sin saltarse la metodología **SPEC → SKILL → CODE**.

## Cuándo usarla

- Al iniciar o reestructurar el proyecto.
- Antes de crear cualquier módulo nuevo (`users`, `avisos`, `notificaciones`, etc.).
- Cuando surjan dudas sobre ubicación de archivos (`controllers`, `models`, `services`, `middleware`).
- Al revisar si una funcionalidad viola separación de responsabilidades.
- Antes de desplegar o documentar la arquitectura en `docs/`.

## SPEC relacionada

- `spec/00-master-spec.md`
- `spec/01-arquitectura.md`
- `spec/03-requisitos-no-funcionales.md`
- `spec/25-criterios-aceptacion.md`

## Pasos de implementación

1. Leer `spec/01-arquitectura.md` y confirmar alcance, actores y restricciones.
2. Verificar que exista esta SKILL; si no, no programar.
3. Crear/validar la estructura raíz obligatoria:

```text
PortalInfor/
├── spec/
├── skills/
├── app/
├── tests/
├── database/
├── docs/
├── storage/
├── uploads/
├── logs/
├── .env.example
├── README.md
└── CHANGELOG.md
```

4. Dentro de `app/`, asegurar capas MVC:

```text
app/
├── config/
├── controllers/
├── models/
├── views/
├── middleware/
├── services/
├── repositories/
├── helpers/
├── validators/
├── routes/
├── ajax/
├── assets/
└── public/
```

5. Definir punto de entrada único (`public/index.php`) y front controller.
6. Configurar carga de entorno (`.env` / `config/`) sin hardcodear secretos.
7. Documentar diagrama de capas y flujo request → middleware → controller → service/repository → model → view/JSON.
8. Validar que no haya lógica de negocio en vistas ni SQL crudo en controllers.
9. Actualizar estado de implementación en la SPEC correspondiente.

## Convenciones de código

- PHP 8.x con tipado estricto (`declare(strict_types=1);`) en clases nuevas.
- Namespaces coherentes con carpetas (`App\Controllers`, `App\Models`, etc.).
- Un controller por recurso principal; métodos cortos y orientados a acción.
- Repositories para acceso a datos; Services para reglas de negocio.
- PDO únicamente para MySQL; sin `mysqli_*` ni concatenación SQL.
- Vistas solo presentan datos; sin consultas ni permisos críticos.
- Rutas centralizadas en `app/routes/`.
- Assets en `app/assets/`; documento público solo vía `public/`.
- Idioma de comentarios y mensajes de usuario: español institucional.

## Checklist de seguridad

- [ ] Secretos solo en `.env` (nunca en repositorio).
- [ ] `uploads/`, `storage/` y `logs/` fuera de ejecución directa de scripts.
- [ ] Middleware de autenticación/autorización en rutas protegidas.
- [ ] CSRF en formularios y endpoints AJAX mutadores.
- [ ] Headers de seguridad básicos (X-Frame-Options, X-Content-Type-Options).
- [ ] Errores detallados solo en entorno local; genéricos en producción.
- [ ] `.htaccess` / Apache bloquea listados y archivos sensibles.

## Checklist de pruebas

- [ ] Front controller resuelve rutas válidas e inválidas.
- [ ] Capas respetan flujo MVC (sin saltos indebidos).
- [ ] Módulo de ejemplo (login o dashboard) carga sin dependencias circulares.
- [ ] Assets CSS/JS se sirven correctamente.
- [ ] Variables de entorno se leen sin exponer valores en respuestas.
- [ ] Documentación de estructura coincide con el árbol real.

## Errores comunes a evitar

- Programar sin SPEC ni SKILL.
- Mezclar lógica de negocio en vistas o en JavaScript del cliente.
- Poner credenciales en código o en `credenciales.md` de producción.
- Acceder a BD desde controllers sin repository/service.
- Exponer `app/` completo como document root (usar `public/`).
- Duplicar configuración en múltiples archivos sin fuente única.
- Crear “utilidades globales” que rompan límites de capas.

## Criterio de done

La arquitectura está **done** cuando: existe la estructura de carpetas obligatoria, el front controller funciona, las capas MVC están definidas y documentadas, no hay secretos en código, la SPEC de arquitectura está actualizada, y cualquier módulo nuevo puede ubicarse sin ambigüedad siguiendo SPEC → SKILL → CODE.
