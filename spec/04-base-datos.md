# SPEC 04 — Base de Datos `ue_san_lorenzo`

**Proyecto:** Unidad Educativa Fiscomisional San Lorenzo (SISTEMA_G_TECNICO_SAN_LORENZO)  
**Metodología:** POLKDEV — SPEC → SKILL → CODE  
**Estado de implementación:** PENDING

---

## 1. Objetivo

Definir el esquema relacional MySQL de la base `ue_san_lorenzo`: tablas, campos, claves primarias/foráneas, índices y restricciones para soportar autenticación, RBAC, avisos, notificaciones, archivos, sesiones y auditoría.

## 2. Alcance

### Incluye
- DDL lógico de tablas: `users`, `roles`, `permissions`, `user_roles`, `role_permissions`, `announcements`, `notifications`, `notification_reads`, `attachments`, `audit_logs`, `sessions`.
- Convenciones de nombres, tipos y motor InnoDB / utf8mb4.

### No incluye
- Scripts físicos finales (van en `database/migrations/`).
- Réplicas o particionado.

## 3. Actores

| Actor | Uso de BD |
|-------|-----------|
| ADMIN | CRUD amplio vía aplicación |
| Servicios app | Lectura/escritura vía PDO |
| Migraciones/Seeders | Creación e inicialización |

## 4. Requisitos

1. Charset `utf8mb4`, collation `utf8mb4_unicode_ci`, engine `InnoDB`.
2. PK enteras `BIGINT UNSIGNED AUTO_INCREMENT` (salvo `sessions.id` string).
3. FKs con integridad referencial; `ON DELETE RESTRICT` o `CASCADE` según tabla.
4. Timestamps `created_at`, `updated_at` donde aplique; soft flags con `status` o `deleted_at` opcional en users.
5. Índices en columnas de búsqueda y FKs.
6. Passwords solo hash; nunca texto plano.

## 5. Flujo funcional

1. Migración crea BD y tablas en orden de dependencias.
2. Seeder inserta roles, permisos, admin inicial.
3. App usa repositorios PDO prepared statements.
4. Flujos: user↔roles↔permissions; announcement → notifications → reads; attachments polimórficos o tipados.

## 6. Reglas de negocio

1. Email y username únicos en `users`.
2. `slug`/`name` únicos en `roles` y `permissions`.
3. No eliminar rol sistema si tiene usuarios (RESTRICT).
4. `notification_reads` único por (`notification_id`, `user_id`).
5. Adjuntos referencian entidad (`attachable_type`, `attachable_id`) o FKs explícitas; se elige modelo polimórfico simple.
6. Sesiones expiradas pueden limpiarse por job/cron o al login.

## 7. Estructura de datos

### 7.1 `users`

| Campo | Tipo | Restricciones |
|-------|------|---------------|
| id | BIGINT UNSIGNED | PK AI |
| username | VARCHAR(50) | UNIQUE NOT NULL |
| email | VARCHAR(150) | UNIQUE NOT NULL |
| password | VARCHAR(255) | NOT NULL (hash) |
| first_name | VARCHAR(100) | NOT NULL |
| last_name | VARCHAR(100) | NOT NULL |
| phone | VARCHAR(30) | NULL |
| status | ENUM('active','inactive','pending') | NOT NULL DEFAULT 'pending' |
| last_login_at | DATETIME | NULL |
| failed_login_attempts | INT UNSIGNED | DEFAULT 0 |
| locked_until | DATETIME | NULL |
| created_at | DATETIME | NOT NULL |
| updated_at | DATETIME | NOT NULL |

**Índices:** UNIQUE(username), UNIQUE(email), INDEX(status), INDEX(last_name, first_name)

### 7.2 `roles`

| Campo | Tipo | Restricciones |
|-------|------|---------------|
| id | BIGINT UNSIGNED | PK AI |
| name | VARCHAR(50) | UNIQUE NOT NULL (ADMIN, RECTOR, ...) |
| display_name | VARCHAR(100) | NOT NULL |
| description | VARCHAR(255) | NULL |
| is_system | TINYINT(1) | DEFAULT 0 |
| created_at | DATETIME | NOT NULL |
| updated_at | DATETIME | NOT NULL |

### 7.3 `permissions`

| Campo | Tipo | Restricciones |
|-------|------|---------------|
| id | BIGINT UNSIGNED | PK AI |
| name | VARCHAR(100) | UNIQUE NOT NULL (ej. users.view) |
| group_name | VARCHAR(50) | NOT NULL (users, roles, ...) |
| description | VARCHAR(255) | NULL |
| created_at | DATETIME | NOT NULL |
| updated_at | DATETIME | NOT NULL |

**Índices:** UNIQUE(name), INDEX(group_name)

### 7.4 `user_roles`

| Campo | Tipo | Restricciones |
|-------|------|---------------|
| id | BIGINT UNSIGNED | PK AI |
| user_id | BIGINT UNSIGNED | FK → users.id ON DELETE CASCADE |
| role_id | BIGINT UNSIGNED | FK → roles.id ON DELETE RESTRICT |
| created_at | DATETIME | NOT NULL |

**Índices:** UNIQUE(user_id, role_id), INDEX(role_id)

### 7.5 `role_permissions`

| Campo | Tipo | Restricciones |
|-------|------|---------------|
| id | BIGINT UNSIGNED | PK AI |
| role_id | BIGINT UNSIGNED | FK → roles.id ON DELETE CASCADE |
| permission_id | BIGINT UNSIGNED | FK → permissions.id ON DELETE CASCADE |
| created_at | DATETIME | NOT NULL |

**Índices:** UNIQUE(role_id, permission_id)

### 7.6 `announcements`

| Campo | Tipo | Restricciones |
|-------|------|---------------|
| id | BIGINT UNSIGNED | PK AI |
| author_id | BIGINT UNSIGNED | FK → users.id ON DELETE RESTRICT |
| title | VARCHAR(200) | NOT NULL |
| description | VARCHAR(500) | NULL |
| content | TEXT | NOT NULL |
| category | VARCHAR(100) | NULL |
| priority | ENUM('low','medium','high') | DEFAULT 'medium' |
| status | ENUM('draft','published','archived') | DEFAULT 'draft' |
| publish_at | DATETIME | NULL |
| expire_at | DATETIME | NULL |
| audience | JSON o VARCHAR(255) | destinatarios (roles/ids) |
| created_at | DATETIME | NOT NULL |
| updated_at | DATETIME | NOT NULL |

**Índices:** INDEX(status), INDEX(publish_at), INDEX(author_id), INDEX(priority)

### 7.7 `notifications`

| Campo | Tipo | Restricciones |
|-------|------|---------------|
| id | BIGINT UNSIGNED | PK AI |
| user_id | BIGINT UNSIGNED | FK → users.id ON DELETE CASCADE (destinatario) |
| announcement_id | BIGINT UNSIGNED | NULL FK → announcements.id ON DELETE SET NULL |
| title | VARCHAR(200) | NOT NULL |
| body | TEXT | NULL |
| priority | ENUM('low','medium','high') | DEFAULT 'medium' |
| type | VARCHAR(50) | DEFAULT 'announcement' |
| created_at | DATETIME | NOT NULL |
| updated_at | DATETIME | NOT NULL |

**Índices:** INDEX(user_id, created_at), INDEX(announcement_id), INDEX(priority)

### 7.8 `notification_reads`

| Campo | Tipo | Restricciones |
|-------|------|---------------|
| id | BIGINT UNSIGNED | PK AI |
| notification_id | BIGINT UNSIGNED | FK → notifications.id ON DELETE CASCADE |
| user_id | BIGINT UNSIGNED | FK → users.id ON DELETE CASCADE |
| read_at | DATETIME | NOT NULL |

**Índices:** UNIQUE(notification_id, user_id), INDEX(user_id, read_at)

> Nota: si `notifications.user_id` ya es el destinatario, `notification_reads` registra el momento de lectura; alternativa: columna `read_at` en `notifications`. Se mantiene tabla separada para extensibilidad multi-lector futuro.

### 7.9 `attachments`

| Campo | Tipo | Restricciones |
|-------|------|---------------|
| id | BIGINT UNSIGNED | PK AI |
| uploaded_by | BIGINT UNSIGNED | FK → users.id ON DELETE RESTRICT |
| attachable_type | VARCHAR(50) | NOT NULL (announcement, notification, user) |
| attachable_id | BIGINT UNSIGNED | NOT NULL |
| original_name | VARCHAR(255) | NOT NULL |
| stored_name | VARCHAR(255) | NOT NULL |
| mime_type | VARCHAR(100) | NOT NULL |
| extension | VARCHAR(20) | NOT NULL |
| size_bytes | INT UNSIGNED | NOT NULL |
| disk_path | VARCHAR(500) | NOT NULL (relativo a storage/uploads) |
| checksum_sha256 | CHAR(64) | NULL |
| created_at | DATETIME | NOT NULL |
| updated_at | DATETIME | NOT NULL |

**Índices:** INDEX(attachable_type, attachable_id), INDEX(uploaded_by), UNIQUE(stored_name)

### 7.10 `audit_logs`

| Campo | Tipo | Restricciones |
|-------|------|---------------|
| id | BIGINT UNSIGNED | PK AI |
| user_id | BIGINT UNSIGNED | NULL FK → users.id ON DELETE SET NULL |
| action | VARCHAR(100) | NOT NULL |
| entity | VARCHAR(50) | NULL |
| entity_id | BIGINT UNSIGNED | NULL |
| ip_address | VARCHAR(45) | NULL |
| user_agent | VARCHAR(255) | NULL |
| properties | JSON | NULL |
| created_at | DATETIME | NOT NULL |

**Índices:** INDEX(user_id, created_at), INDEX(entity, entity_id), INDEX(action)

### 7.11 `sessions`

| Campo | Tipo | Restricciones |
|-------|------|---------------|
| id | VARCHAR(128) | PK |
| user_id | BIGINT UNSIGNED | NULL FK → users.id ON DELETE CASCADE |
| ip_address | VARCHAR(45) | NULL |
| user_agent | VARCHAR(255) | NULL |
| payload | TEXT | NOT NULL |
| last_activity | INT UNSIGNED | NOT NULL |

**Índices:** INDEX(user_id), INDEX(last_activity)

> Puede usarse manejador de sesión PHP file-based en fase 1; tabla `sessions` lista para driver DB.

### Diagrama de relaciones (texto)

```text
users 1─N user_roles N─1 roles 1─N role_permissions N─1 permissions
users 1─N announcements
users 1─N notifications
notifications 1─N notification_reads N─1 users
announcements 1─N notifications
* 1─N attachments (polimórfico)
users 1─N audit_logs
users 1─N sessions
```

## 8. Validaciones

- Longitudes respetan VARCHAR definidos.
- ENUMs solo valores listados.
- FKs validadas antes de insert.
- `audience` JSON bien formado si se usa JSON.
- `size_bytes` > 0 y ≤ límite config.

## 9. Seguridad

- Usuario DB de app con privilegios mínimos (SELECT/INSERT/UPDATE/DELETE en schema; sin DROP en prod).
- Credenciales en `.env`.
- Prepared statements obligatorios.
- No exponer rutas `disk_path` crudas al cliente; usar id de attachment.

## 10. Interfaz

No UI directa; pantallas de admin reflejan campos vía formularios/tablas alineados a este esquema.

## 11. AJAX requerido

Endpoints de listado deben proyectar solo columnas necesarias (evitar SELECT * en producción cuando haya TEXT grandes). Para avisos, listar sin `content` completo en índice.

## 12. Respuestas esperadas

Migración exitosa:
- BD `ue_san_lorenzo` existe.
- 11 tablas creadas con FKs.
Seeder:
- Roles base + permisos + usuario ADMIN.

## 13. Manejo de errores

| Error SQL | Manejo app |
|-----------|------------|
| Duplicate entry | 409/400 mensaje amigable |
| FK fail | 400 referencia inválida |
| Connection fail | 500 + log; mensaje “servicio no disponible” |

## 14. Casos de prueba

| ID | Caso | Esperado |
|----|------|----------|
| DB-01 | Crear user email duplicado | Error UNIQUE |
| DB-02 | Asignar rol inexistente | FK fail |
| DB-03 | Borrar announcement con notifications | SET NULL o CASCADE según FK |
| DB-04 | UNIQUE notification_reads | Segundo insert falla |
| DB-05 | Índices explican búsqueda email | Uso de índice |

## 15. Criterios de aceptación

- [ ] Script/migración crea todas las tablas listadas.
- [ ] PK/FK/índices documentados e implementados.
- [ ] Seed roles ADMIN, RECTOR, VICERRECTOR, DOCENTE.
- [ ] utf8mb4 + InnoDB.
- [ ] Password solo hash en `users.password`.

## 16. Dependencias

- MySQL/MariaDB en XAMPP.
- SPEC 05, 07–12 para uso de campos.
- `skills/mysql/SKILL.md`, `skills/migrations/SKILL.md`, `skills/seeders/SKILL.md`.

## 17. SKILL requerida

- `skills/mysql/SKILL.md`
- `skills/migrations/SKILL.md`
- `skills/seeders/SKILL.md`

## 18. Estado de implementación

**PENDING**
