---
name: migrations
description: Crea y aplica migraciones versionadas MySQL para PortalInfor (up/down, FKs, utf8mb4). Usar al cambiar esquema, añadir tablas o preparar base limpia en XAMPP.
---

# Migraciones — PortalInfor

## Nombre

`migrations`

## Propósito

Gestionar el esquema de base de datos de forma versionada, repetible y reversible (cuando sea posible), alineado a `spec/04-base-datos.md`, sin editar a mano una BD compartida de forma no trazable.

## Cuándo usarla

- Al crear tablas nuevas (`users`, `roles`, `announcements`, `notifications`, `files`, `audit_logs`, etc.).
- Al alterar columnas, índices o FKs.
- Al preparar entorno limpio en XAMPP.
- Antes de seeders (migrar primero, seedear después).
- Al documentar cambios de esquema en CHANGELOG.

## SPEC relacionada

- `spec/18-migraciones.md`
- `spec/04-base-datos.md`
- `spec/19-seeders.md`
- `spec/22-auditoria.md` (tabla audit)

## Pasos de implementación

1. Leer SPEC de datos y de migraciones.
2. Crear archivo en `database/migrations/` con timestamp + nombre descriptivo:

```text
2026_09_08_000001_create_users_table.php
```

3. Implementar métodos `up()` y `down()` (o SQL `up.sql`/`down.sql` según convención del proyecto).
4. Usar InnoDB + `utf8mb4_unicode_ci`.
5. Declarar PK, uniques, índices y FKs explícitas.
6. Registrar migración aplicada en tabla `migrations` (batch).
7. Ejecutar runner CLI o script PHP documentado en README (`php database/migrate.php` o equivalente).
8. Verificar en phpMyAdmin/MySQL que el esquema coincide.
9. Actualizar SPEC `04` y `18` (estado).
10. No editar migraciones ya aplicadas en equipos compartidos: crear una nueva.

## Convenciones de código

- Un cambio lógico por migración (evitar “mega migration” mezclando módulos no relacionados cuando sea evitable).
- Nombres: `create_*_table`, `add_*_to_*`, `create_*_pivot`.
- Orden: tablas padre antes que hijas.
- FKs con `ON DELETE`/`ON UPDATE` según reglas de negocio (RESTRICT por defecto).
- Incluir `created_at` / `updated_at` cuando aplique.
- SQL idempotente solo si el runner lo soporta; preferir control por tabla `migrations`.
- Nunca poner DML masivo de negocio en migraciones (eso es seeder), salvo data mínima estructural (roles base) si SPEC lo permite — preferir seeders.

## Checklist de seguridad

- [ ] Script de migrate no expuesto vía URL pública.
- [ ] Credenciales desde `.env`.
- [ ] Sin `DROP DATABASE` en migraciones de app.
- [ ] `down()` no destruye datos de producción sin control humano.
- [ ] Revisar privilegios del usuario MySQL (DDL solo en deploy controlado).

## Checklist de pruebas

- [ ] `up` en BD vacía OK.
- [ ] Segunda ejecución no duplica (runner detecta aplicadas).
- [ ] `down` revierte sin error en local.
- [ ] FKs funcionan (insert huérfano falla).
- [ ] Charset utf8mb4 correcto.
- [ ] Orden de migraciones respeta dependencias.
- [ ] Seeders corren después sin error de esquema.

## Errores comunes a evitar

- Alterar a mano la BD y olvidar migración.
- Editar migración antigua ya aplicada por otros.
- Olvidar índices en FKs/columnas de filtro.
- Cascades peligrosos sin regla de negocio.
- Mezclar seeds grandes dentro de `up()`.
- Nombres de archivo sin orden lexicográfico/timestamp.

## Criterio de done

Migraciones está **done** cuando el cambio de esquema está versionado, aplica en BD limpia, tiene `down` usable en local, queda registrado, coincide con la SPEC de datos, y el runner no es accesible públicamente.
