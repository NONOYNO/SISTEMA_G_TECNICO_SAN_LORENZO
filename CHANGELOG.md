# Changelog

Todos los cambios relevantes del proyecto SISTEMA_G_TECNICO_SAN_LORENZO se documentan en este archivo.

## [0.2.0] - 2026-09-08

### Added

- 26 especificaciones en `spec/` y 19 skills en `skills/` (POLKDEV).
- Migraciones MySQL (11 tablas) y seeders RBAC + usuarios de prueba.
- Autenticación completa: login usuario/correo, registro, bloqueo por intentos, CSRF, auditoría.
- Módulos: usuarios, roles/permisos, avisos (publicar/archivar + fan-out), notificaciones, archivos seguros, dashboard por rol, perfil, auditoría.
- Documentación en `docs/` y suite `php tests/run.php` (12 tests).
- Seeder demo de aviso institucional.

## [0.1.0] - 2026-09-08

### Added

- Núcleo MVC PHP 8, Router, middleware, layouts Bootstrap institucionales.
- Estructura inicial del repositorio.
