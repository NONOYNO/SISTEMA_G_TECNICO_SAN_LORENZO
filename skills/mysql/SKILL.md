---
name: mysql
description: Diseño y acceso MySQL/MariaDB con PDO para SISTEMA_G_TECNICO_SAN_LORENZO (esquema, índices, FKs, consultas preparadas, integridad). Usar al modelar tablas, escribir queries, optimizar o revisar la capa de datos.
---

# MySQL / PDO — SISTEMA_G_TECNICO_SAN_LORENZO

## Nombre

`mysql`

## Propósito

Guiar el diseño del esquema MySQL/MariaDB y el acceso seguro mediante PDO para el portal institucional: tablas, relaciones, índices, migraciones alineadas y consultas preparadas sin inyección SQL.

## Cuándo usarla

- Al diseñar o alterar tablas del sistema.
- Al escribir queries en repositories/models.
- Al crear migraciones o seeders relacionados.
- Al diagnosticar lentitud, integridad referencial o errores SQL.
- Antes de exponer reportes o listados paginados.

## SPEC relacionada

- `spec/04-base-datos.md`
- `spec/18-migraciones.md`
- `spec/19-seeders.md`
- `spec/05-seguridad.md`

## Pasos de implementación

1. Leer `spec/04-base-datos.md` (entidades, relaciones, reglas).
2. Definir tablas con PK, FK, índices y `ENGINE=InnoDB` / `utf8mb4`.
3. Nombrar tablas en plural snake_case (`users`, `role_permissions`, `audit_logs`).
4. Crear migración versionada en `database/migrations/` (skill `migrations`).
5. Configurar PDO en `app/config/` con DSN, usuario, charset `utf8mb4`, `ERRMODE_EXCEPTION`, `FETCH_ASSOC`, `EMULATE_PREPARES=false`.
6. Implementar acceso solo vía prepared statements / bind.
7. Aplicar soft-delete o flags de estado cuando la SPEC lo indique (`is_active`, `deleted_at`).
8. Probar CRUD, FKs y cascadas/restricciones.
9. Documentar diagrama ER actualizado en `docs/` o en la SPEC.

## Convenciones de código

- Charset: `utf8mb4` / collation `utf8mb4_unicode_ci`.
- Tipos: `INT UNSIGNED` para IDs; `VARCHAR` con longitudes justificadas; `TEXT` para contenido largo; `DATETIME` o `TIMESTAMP` según SPEC.
- Nombres de columnas snake_case (`created_at`, `updated_at`).
- FK: `tabla_id` (ej. `user_id`, `role_id`).
- Índices en columnas de búsqueda/filtro frecuentes (`email`, `username`, `status`).
- Únicos en `email`, `username` y claves de negocio.
- Transacciones (`beginTransaction` / `commit` / `rollBack`) en operaciones multi-tabla.
- Nunca `SELECT *` en listados grandes; seleccionar columnas necesarias.
- Paginación con `LIMIT`/`OFFSET` o keyset según volumen.
- Repositories encapsulan SQL; no SQL en vistas ni JS.

## Checklist de seguridad

- [ ] Solo prepared statements / parámetros enlazados.
- [ ] Usuario de BD con privilegios mínimos (no root en producción).
- [ ] Credenciales en `.env`, no en código.
- [ ] Sin procedimientos dinámicos con input crudo.
- [ ] Validar IDs y filtros antes del query.
- [ ] Evitar exposición de mensajes SQL al cliente.
- [ ] Backups periódicos (ver deployment/backup SPEC).

## Checklist de pruebas

- [ ] Migración aplica en MySQL limpia sin errores.
- [ ] FKs rechazan huérfanos.
- [ ] Uniques bloquean duplicados.
- [ ] CRUD básico por entidad crítica funciona.
- [ ] Transacción revierte ante fallo parcial.
- [ ] Charset acepta tildes/ñ/emoji si aplica.
- [ ] Consultas de listado con filtro/paginación correctas.

## Errores comunes a evitar

- Usar MyISAM o tablas sin FK cuando se requiere integridad.
- Concatenar variables en SQL.
- Guardar contraseñas en texto plano o hash débil.
- Olvidar índices en joins frecuentes.
- Cascade DELETE agresivo sin regla de negocio.
- Mezclar lógica de permisos solo en BD sin validación app.
- Ejecutar seeders de prueba en producción.

## Criterio de done

MySQL está **done** cuando el esquema coincide con `spec/04-base-datos.md`, las FKs/índices existen, PDO está configurado de forma segura, las consultas del módulo usan prepared statements, y migraciones/pruebas demuestran integridad y charset correctos.
