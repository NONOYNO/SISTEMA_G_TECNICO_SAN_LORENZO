# SPEC 19 — Seeders

**Proyecto:** Unidad Educativa Fiscomisional San Lorenzo  
**Stack:** PHP 8 + MySQL + PDO + MVC + Bootstrap 5 + AJAX + XAMPP  
**Módulo:** Datos semilla para desarrollo y pruebas  
**Estado:** PENDING

---

## 1. Objetivo

Definir seeders PHP que inserten roles, permisos, relaciones RBAC y usuarios de prueba del portal, permitiendo validar autenticación y autorización de punta a punta en XAMPP.

## 2. Alcance

**Incluye:**
- `database/seeders/` con seeders ejecutables vía CLI (y web dev opcional).
- Roles: ADMIN, RECTOR, VICERRECTOR, DOCENTE.
- Catálogo de permisos granulares.
- Asignación `role_permissions`.
- Usuarios de prueba con contraseñas hasheadas.
- Generación/actualización de `credenciales.md` (solo entorno local).
- Flag `--fresh` conceptual: migrar + seed (opcional).

**Excluye:**
- Datos reales de producción.
- Seed automático en cada request HTTP.

## 3. Actores

| Actor | Uso |
|-------|-----|
| Desarrollador / QA | Ejecuta seeders en local |
| ADMIN | No ejecuta seeders en producción |

## 4. Requisitos

### Funcionales
- RF-S01: Seeder de roles inserta exactamente los 4 roles del sistema.
- RF-S02: Seeder de permisos crea el catálogo completo usado por la app.
- RF-S03: Seeder RBAC asigna permisos por rol según matriz.
- RF-S04: Seeder de usuarios crea 4 cuentas de prueba.
- RF-S05: Contraseñas con `password_hash(PASSWORD_DEFAULT)`.
- RF-S06: Idempotencia: re-ejecutar no duplica (upsert por email/username/code).
- RF-S07: Documentar credenciales en `credenciales.md`.

### Usuarios de prueba obligatorios

| Rol | Correo | Contraseña |
|-----|--------|------------|
| ADMIN | `admin@uesanlorenzo.edu` | `Admin123!` |
| RECTOR | `rector@uesanlorenzo.edu` | `Rector123!` |
| VICERRECTOR | `vicerrector@uesanlorenzo.edu` | `Vicerrector123!` |
| DOCENTE | `docente@uesanlorenzo.edu` | `Docente123!` |

Usernames sugeridos: `admin`, `rector`, `vicerrector`, `docente`.

## 5. Flujo funcional

```text
1. Verificar migraciones aplicadas
2. php database/seed.php (o seeders runner)
3. SeedRoles → SeedPermissions → SeedRolePermissions → SeedUsers
4. (opcional) SeedDemoAnnouncements
5. Escribir/actualizar credenciales.md
6. Salida: resumen de registros
```

## 6. Reglas de negocio

- RN-S01: Solo para `APP_ENV=local|testing`.
- RN-S02: En producción el comando debe abortar salvo `--force` explícito documentado (desaconsejado).
- RN-S03: Matriz de permisos por defecto:

| Permiso | ADMIN | RECTOR | VICERRECTOR | DOCENTE |
|---------|-------|--------|-------------|---------|
| users.view | ✓ | ✓* | ✗ | ✗ |
| users.create/edit/delete | ✓ | ✗ | ✗ | ✗ |
| roles.* | ✓ | ✗ | ✗ | ✗ |
| announcements.view | ✓ | ✓ | ✓ | ✓ |
| announcements.create/edit/publish | ✓ | ✗ | ✓ | ✗ |
| announcements.delete | ✓ | ✗ | ✓* | ✗ |
| notifications.view | ✓ | ✓ | ✓ | ✓ |
| notifications.create | ✓ | ✗ | ✓ | ✗ |
| files.upload | ✓ | ✗ | ✓ | ✗ |
| files.download | ✓ | ✓ | ✓ | ✓ |
| files.delete | ✓ | ✗ | ✓* | ✗ |
| reports.view | ✓ | ✓ | ✗ | ✗ |
| audit.view | ✓ | ✗ | ✗ | ✗ |
| settings.manage | ✓ | ✗ | ✗ | ✗ |

\* = opcional según política; documentar en seeder.

- RN-S04: Usuarios seed con `status=active`.
- RN-S05: Nunca guardar contraseñas en texto plano en DB.
- RN-S06: `credenciales.md` en `.gitignore` si contiene secretos; mantener `credenciales.example.md` sin passwords reales si se prefiere — el prompt master exige `credenciales.md` para pruebas.

## 7. Estructura de datos

### Inserciones
```text
roles: code, name, description
permissions: code, name, module
role_permissions: role_id, permission_id
users: name, email, username, password_hash, status
user_roles: user_id, role_id
```

### Ejemplo hash
```php
password_hash('Admin123!', PASSWORD_DEFAULT);
```

## 8. Validaciones

- Migraciones previas existen (FK).
- Emails únicos.
- Roles codes exactos: `ADMIN|RECTOR|VICERRECTOR|DOCENTE`.
- Permisos codes en snake con punto (`users.view`).
- Abortar si DB no responde.

## 9. Seguridad

- Deshabilitar seeders web en producción.
- No loguear contraseñas en texto plano.
- Restringir acceso HTTP a `/database`.
- Usuarios seed solo para desarrollo; cambiar en staging real.
- Advertencia visible en consola: “CREDENTIALS FOR DEV ONLY”.

## 10. Interfaz

- CLI prioritario.
- Web seed (opcional): botón “Ejecutar seeders” en panel dev ADMIN, con confirmación doble.
- `credenciales.md` legible en Markdown.

## 11. AJAX requerido

Opcional solo en runner web:

| Endpoint | Método | Uso |
|----------|--------|-----|
| `/ajax/admin/seeders/run` | POST | Ejecutar seeders (local) |

## 12. Respuestas esperadas

**CLI:**
```text
Seeded roles: 4
Seeded permissions: 24
Seeded role_permissions: ...
Seeded users: 4
Wrote credenciales.md
Done.
```

**JSON:**
```json
{
  "success": true,
  "message": "Seeders ejecutados",
  "data": { "users": 4, "roles": 4 }
}
```

## 13. Manejo de errores

- FK failure → indicar migraciones faltantes.
- Duplicate → upsert o skip con warning.
- Entorno producción → exit code 1.
- Fallo escritura `credenciales.md` → warning, seed DB igual puede OK.

## 14. Casos de prueba

| ID | Caso | Esperado |
|----|------|----------|
| SEED-001 | Seed limpio | 4 roles, 4 users |
| SEED-002 | Re-seed | Sin duplicados |
| SEED-003 | Login admin@uesanlorenzo.edu / Admin123! | OK dashboard ADMIN |
| SEED-004 | Login rector... | Dashboard RECTOR |
| SEED-005 | Login vicerrector... | Puede publicar |
| SEED-006 | Login docente... | Solo consulta |
| SEED-007 | Password en DB | Hash, no texto plano |
| SEED-008 | Seed en production sin force | Abort |

## 15. Criterios de aceptación

- [ ] Roles y permisos sembrados según matriz.
- [ ] Cuatro usuarios de prueba con correos `@uesanlorenzo.edu` y claves indicadas.
- [ ] Login funciona para los 4 roles.
- [ ] `credenciales.md` generado/actualizado.
- [ ] Idempotencia verificada.
- [ ] Protegido fuera de local.

## 16. Dependencias

- `spec/18-migraciones.md`, `spec/09-roles-permisos.md`, `spec/08-usuarios.md`, `spec/07-autenticacion.md`
- Skills: `seeders`, `mysql`, `authentication`

## 17. SKILL requerida

```text
skills/seeders/SKILL.md
```

## 18. Estado de implementación

**PENDING**
