# SPEC 18 — Sistema de migraciones

**Proyecto:** Unidad Educativa Fiscomisional San Lorenzo  
**Stack:** PHP 8 + MySQL + PDO + MVC + Bootstrap 5 + AJAX + XAMPP  
**Módulo:** Migraciones de base de datos  
**Estado:** PENDING

---

## 1. Objetivo

Implementar un sistema de migraciones versionadas en PHP (CLI y/o runner web protegido) que cree y evolucione el esquema MySQL de forma ordenada, repetible y con rollback conceptual, usando naming `YYYYMMDDHHMMSS_descripcion.php`.

## 2. Alcance

**Incluye:**
- Carpeta `database/migrations/`.
- Tabla de control `migrations`.
- Runner CLI (`php database/migrate.php` o equivalente).
- Runner web opcional solo en entorno local/dev con autenticación ADMIN y token.
- Comandos: `migrate`, `status`, `rollback` (último batch).
- Convención de nombres y plantilla `up()` / `down()`.

**Excluye:**
- Seeders (ver `spec/19-seeders.md`).
- Backups completos (ver `spec/23-backup.md`).
- Migraciones automáticas en producción sin supervisión.

## 3. Actores

| Actor | Uso |
|-------|-----|
| Desarrollador | Ejecuta CLI en XAMPP |
| ADMIN (dev) | Puede usar runner web si está habilitado |
| Producción | Solo personal autorizado; runner web deshabilitado |

## 4. Requisitos

### Funcionales
- RF-M01: Detectar archivos `database/migrations/*.php` ordenados por nombre ASC.
- RF-M02: Ejecutar solo migraciones pendientes.
- RF-M03: Registrar cada migración aplicada en tabla `migrations` (id, migration, batch, applied_at).
- RF-M04: Agrupar ejecuciones en `batch` incremental.
- RF-M05: `rollback` revierte el último batch llamando `down()`.
- RF-M06: `status` lista aplicadas vs pendientes.
- RF-M07: Transacción por migración cuando el motor lo permita (DDL en MySQL puede hacer commit implícito: documentar limitación).
- RF-M08: Naming obligatorio: `YYYYMMDDHHMMSS_descripcion.php` (ej. `20260908153000_create_users.php`).

### No funcionales
- RNF-M01: Idempotencia: no reaplicar la misma migración.
- RNF-M02: Mensajes claros en consola.
- RNF-M03: Fallo detiene el proceso y reporta archivo/error.

## 5. Flujo funcional

### migrate
```text
1. Conectar PDO (.env)
2. Asegurar tabla migrations
3. Listar archivos ordenados
4. Filtrar no aplicadas
5. batch = max(batch)+1
6. Por cada pendiente:
   - require archivo
   - llamar up($pdo)
   - insertar en migrations
7. Resumen OK / FAIL
```

### rollback
```text
1. Obtener max(batch)
2. Listar migraciones de ese batch DESC
3. Por cada una: down($pdo) + delete registro
4. Resumen
```

## 6. Reglas de negocio

- RN-M01: Una migración = un archivo = una clase o funciones `up`/`down`.
- RN-M02: `down()` debe revertir lo esencial de `up()` (drop table/index/column).
- RN-M03: No editar migraciones ya aplicadas en entornos compartidos; crear una nueva.
- RN-M04: Runner web: `APP_ENV=local` + ADMIN + CSRF + secreto `MIGRATION_TOKEN`.
- RN-M05: Prohibido exponer runner en producción pública.
- RN-M06: Foreign keys: crear tablas padre antes que hijas; en `down()` invertir orden.
- RN-M07: Charset `utf8mb4`, engine `InnoDB`.

## 7. Estructura de datos

### Tabla `migrations`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT AI PK | |
| migration | VARCHAR(255) UNIQUE | Nombre archivo sin path |
| batch | INT | Grupo de ejecución |
| applied_at | DATETIME | Timestamp |

### Plantilla de archivo
```php
<?php
// 20260908153000_create_users.php
return new class {
    public function up(PDO $pdo): void { /* DDL */ }
    public function down(PDO $pdo): void { /* revert */ }
};
```

### Migraciones iniciales sugeridas (orden conceptual)
```text
YYYYMMDDHHMMSS_create_migrations_meta.php  (si no se crea en runner)
YYYYMMDDHHMMSS_create_users.php
YYYYMMDDHHMMSS_create_roles.php
YYYYMMDDHHMMSS_create_permissions.php
YYYYMMDDHHMMSS_create_user_roles.php
YYYYMMDDHHMMSS_create_role_permissions.php
YYYYMMDDHHMMSS_create_announcements.php
YYYYMMDDHHMMSS_create_notifications.php
YYYYMMDDHHMMSS_create_notification_reads.php
YYYYMMDDHHMMSS_create_attachments.php
YYYYMMDDHHMMSS_create_audit_logs.php
YYYYMMDDHHMMSS_create_sessions.php
YYYYMMDDHHMMSS_create_settings.php
```

## 8. Validaciones

- Nombre archivo cumple regex `^\d{14}_[a-z0-9_]+\.php$`.
- Archivo debe exponer `up` y `down`.
- Credenciales DB presentes en `.env`.
- Rollback sin batches → mensaje “Nothing to rollback”.
- Web runner: validar token y método POST.

## 9. Seguridad

- CLI preferido.
- Web runner deshabilitado por defecto (`MIGRATIONS_WEB=false`).
- No aceptar path de migración desde input usuario (solo scan directorio).
- No mostrar credenciales DB en errores.
- Logs en `logs/database.log`.
- Restringir acceso Apache al directorio `database/` (deny).

## 10. Interfaz

- CLI: salida texto con colores opcionales.
- Web (dev): página simple Bootstrap “Migraciones” con botones Status / Migrate / Rollback + confirmación.
- Sin UI en menú producción.

## 11. AJAX requerido

Solo si existe runner web:

| Endpoint | Método | Uso |
|----------|--------|-----|
| `/ajax/admin/migrations/status` | GET | Estado |
| `/ajax/admin/migrations/run` | POST | migrate |
| `/ajax/admin/migrations/rollback` | POST | rollback |

CLI no usa AJAX.

## 12. Respuestas esperadas

**CLI éxito:**
```text
Migrated: 20260908153000_create_users.php
Migrated: 20260908153100_create_roles.php
Done. Batch 1 (2 migrations)
```

**JSON web:**
```json
{
  "success": true,
  "message": "Migraciones aplicadas",
  "data": { "batch": 2, "applied": ["20260908160000_add_phone_to_users.php"] }
}
```

## 13. Manejo de errores

- Error SQL: abortar, no marcar migración como aplicada.
- Archivo inválido: reportar y salir ≠ 0.
- Rollback `down()` falla: detener y alertar inconsistencia (requiere intervención manual + backup).
- DB inaccesible: mensaje claro de configuración.

## 14. Casos de prueba

| ID | Caso | Esperado |
|----|------|----------|
| MIG-001 | migrate en DB vacía | Tablas creadas + registros |
| MIG-002 | migrate otra vez | 0 pendientes |
| MIG-003 | rollback | down ejecutado, tablas revertidas del batch |
| MIG-004 | Nombre inválido | Ignorado o error explícito |
| MIG-005 | up() falla a mitad | No queda como aplicada |
| MIG-006 | Web con MIGRATIONS_WEB=false | 404/403 |
| MIG-007 | Orden FK | Sin error de referencia |
| MIG-008 | status | Lista correcta |

## 15. Criterios de aceptación

- [ ] Naming `YYYYMMDDHHMMSS_descripcion.php` aplicado.
- [ ] CLI migrate/status/rollback operativos.
- [ ] Tabla `migrations` controla estado.
- [ ] Rollback conceptual del último batch funciona.
- [ ] Runner web protegido o deshabilitado.
- [ ] Esquema base del portal reproducible desde cero.

## 16. Dependencias

- `spec/04-base-datos.md`, `spec/01-arquitectura.md`, `spec/19-seeders.md`, `spec/24-despliegue-xampp.md`
- Skills: `migrations`, `mysql`, `php`

## 17. SKILL requerida

```text
skills/migrations/SKILL.md
```

## 18. Estado de implementación

**PENDING**
