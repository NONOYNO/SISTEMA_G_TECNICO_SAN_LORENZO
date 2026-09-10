# SPEC 00 — Master Spec: SISTEMA_G_TECNICO_SAN_LORENZO

**Proyecto:** Unidad Educativa Fiscomisional San Lorenzo (SISTEMA_G_TECNICO_SAN_LORENZO)  
**Metodología:** POLKDEV — SPEC → SKILL → CODE  
**Principio rector:** SPEC FIRST + SKILL FIRST + CODE SECOND  
**Estado de implementación:** PENDING

---

## 1. Objetivo

Definir la visión general, alcance institucional, módulos, roles, stack tecnológico y reglas de gobernanza del desarrollo del portal informativo-administrativo de la Unidad Educativa Fiscomisional San Lorenzo, de modo que ningún módulo se implemente sin especificación (`spec/`) ni habilidad (`skills/`) previa.

## 2. Alcance

### Incluye
- Autenticación y control de sesión.
- Gestión de usuarios, roles y permisos (RBAC).
- Publicación y administración de avisos institucionales.
- Notificaciones internas con lectura y adjuntos.
- Carga y descarga segura de archivos.
- Dashboards por rol.
- Perfiles de usuario (ADMIN, RECTOR, VICERRECTOR, DOCENTE).
- Auditoría de acciones sensibles.
- Diseño responsive con Bootstrap 5.
- Operación local en XAMPP (Apache + MySQL + PHP 8).

### No incluye (fase actual)
- Integración con sistemas externos (SIE, correo masivo SMTP productivo, SMS).
- App móvil nativa.
- Calificaciones, asistencia o nómina académica.
- Chat en tiempo real (WebSockets).

## 3. Actores

| Rol | Descripción | Acceso típico |
|-----|-------------|----------------|
| **ADMIN** | Administrador técnico del portal | Usuarios, roles, permisos, avisos, notificaciones, archivos, auditoría, configuración |
| **RECTOR** | Autoridad máxima institucional | Dashboards institucionales, avisos, notificaciones, reportes, auditoría de lectura |
| **VICERRECTOR** | Apoyo directivo académico/administrativo | Avisos, notificaciones, gestión parcial de usuarios docentes, reportes |
| **DOCENTE** | Personal docente | Lectura de avisos/notificaciones, perfil propio, descarga de archivos autorizados |
| **Visitante** | No autenticado | Solo pantalla de login / registro (si está habilitado) |

## 4. Requisitos

1. El sistema debe ser una aplicación web MVC en PHP 8 con MySQL vía PDO.
2. Toda funcionalidad nueva debe tener SPEC y SKILL antes de código.
3. El control de acceso debe basarse en roles y permisos granulares.
4. La interfaz debe usar Bootstrap 5 y paleta institucional celeste/azul/blanco.
5. Las operaciones interactivas prioritarias deben usar AJAX con respuestas JSON estandarizadas.
6. Los archivos deben almacenarse fuera de `public/` y validarse por extensión y MIME.
7. Debe existir registro de auditoría para acciones críticas.
8. Debe operar correctamente en XAMPP (Windows/Linux local).

## 5. Flujo funcional

```text
1. Usuario accede a /public (front controller)
2. Router resuelve ruta → Middleware (auth, CSRF, permisos)
3. Controller valida entrada → Service/Repository → Model/BD
4. Respuesta: View HTML o JSON AJAX
5. Acciones sensibles → AuditLog
```

Flujos de negocio principales:
1. Login → Dashboard según rol.
2. ADMIN/RECTOR/VICERRECTOR publica aviso → se generan notificaciones a destinatarios.
3. DOCENTE consulta cards de notificaciones/avisos → marca lectura → descarga adjuntos si aplica.
4. ADMIN gestiona usuarios/roles → auditoría registra cambios.

## 6. Reglas de negocio

1. **SPEC FIRST:** prohibido implementar sin `spec/XX-*.md` y SKILL asociada.
2. Un usuario puede tener uno o más roles; la unión de permisos define el acceso.
3. Solo usuarios activos (`status = active`) pueden autenticarse.
4. Avisos en estado `draft` no son visibles para destinatarios finales.
5. Notificaciones no leídas deben destacarse visualmente.
6. Passwords nunca se almacenan en texto plano.
7. Soft-delete preferido en usuarios (desactivar) frente a borrado físico.
8. Toda descarga de archivo requiere autenticación y permiso `files.download` (o equivalente).

## 7. Estructura de datos

Base de datos: `ue_san_lorenzo`.

Entidades núcleo:
- `users`, `roles`, `permissions`, `user_roles`, `role_permissions`
- `announcements`, `notifications`, `notification_reads`
- `attachments`, `audit_logs`, `sessions`

Detalle completo en `spec/04-base-datos.md`.

## 8. Validaciones

- Validación de entrada en capa validator + revalidación en controller/service.
- Emails, usernames, longitudes, enums de estado/prioridad.
- CSRF en formularios y endpoints AJAX mutadores.
- Permisos verificados en middleware antes de ejecutar acción.

## 9. Seguridad

- Sesiones PHP endurecidas, regeneración de ID en login.
- `password_hash` / `password_verify` (PASSWORD_DEFAULT / bcrypt/argon2).
- PDO prepared statements (prevención SQLi).
- Escape de salida (prevención XSS).
- Headers de seguridad básicos.
- Uploads con whitelist, MIME check, rename, path traversal blocked.
- Detalle en `spec/05-seguridad.md`.

## 10. Interfaz

- Layout: navbar superior + sidebar (desktop) + offcanvas (móvil).
- Cards Bootstrap para avisos, notificaciones y KPIs de dashboard.
- Paleta: celeste (#4FC3F7 / #0288D1), azul (#0D47A1), blanco (#FFFFFF), grises de apoyo.
- Tipografía legible; iconografía Font Awesome (o equivalente).
- Detalle en `spec/06-ui-ux.md`.

## 11. AJAX requerido

Endpoints AJAX tipificados por módulo (login, usuarios, avisos, notificaciones, archivos). Formato de respuesta:

```json
{
  "success": true,
  "message": "Operación exitosa",
  "data": {},
  "errors": {}
}
```

## 12. Respuestas esperadas

| Código HTTP | Uso |
|-------------|-----|
| 200 | OK / operación exitosa |
| 201 | Recurso creado |
| 400 | Validación fallida |
| 401 | No autenticado |
| 403 | Sin permiso |
| 404 | No encontrado |
| 419 | CSRF inválido |
| 429 | Demasiados intentos (brute force) |
| 500 | Error interno controlado |

## 13. Manejo de errores

- Errores de usuario: mensajes claros en español.
- Errores técnicos: log en `logs/` sin exponer stack al cliente en producción.
- Fallos AJAX: toast/alert Bootstrap + `errors` por campo cuando aplique.
- Excepciones de BD: capturadas en service/repository, rethrow tipado o respuesta 500 segura.

## 14. Casos de prueba

| ID | Caso | Resultado esperado |
|----|------|--------------------|
| MS-01 | Acceso sin sesión a ruta protegida | Redirect a login / 401 AJAX |
| MS-02 | Login ADMIN válido | Dashboard admin |
| MS-03 | DOCENTE intenta CRUD usuarios | 403 |
| MS-04 | Publicar aviso con destinatarios | Notificaciones creadas |
| MS-05 | Intento de subir `.php` | Rechazo |
| MS-06 | Implementar feature sin SPEC | Bloqueado por metodología (revisión) |

## 15. Criterios de aceptación

- [ ] Existen las SPECs 00–12 en `spec/` con las 18 secciones.
- [ ] Stack documentado: PHP 8 + MySQL + PDO + MVC + Bootstrap 5 + AJAX + XAMPP.
- [ ] Roles ADMIN, RECTOR, VICERRECTOR, DOCENTE definidos y mapeados a módulos.
- [ ] Principio SPEC FIRST explícito y aplicable en el flujo de desarrollo.
- [ ] Módulos listados con referencia a su SPEC dedicada.

## 16. Dependencias

- Entorno XAMPP con PHP 8.x y MySQL/MariaDB.
- Extensiones PHP: pdo_mysql, mbstring, fileinfo, openssl, session.
- Bootstrap 5 (CDN o assets locales).
- Carpetas futuras: `skills/`, `app/`, `database/`, `storage/`, `uploads/`, `logs/`.

## 17. SKILL requerida

- `skills/architecture/SKILL.md`
- `skills/php/SKILL.md`
- `skills/mysql/SKILL.md`
- `skills/mvc/SKILL.md`
- `skills/security/SKILL.md`
- `skills/bootstrap-ui/SKILL.md`
- `skills/ajax/SKILL.md`

## 18. Estado de implementación

**PENDING**

---

## Mapa de módulos → SPEC

| Módulo | SPEC |
|--------|------|
| Arquitectura | `01-arquitectura.md` |
| RF | `02-requisitos-funcionales.md` |
| RNF | `03-requisitos-no-funcionales.md` |
| Base de datos | `04-base-datos.md` |
| Seguridad | `05-seguridad.md` |
| UI/UX | `06-ui-ux.md` |
| Autenticación | `07-autenticacion.md` |
| Usuarios | `08-usuarios.md` |
| Roles y permisos | `09-roles-permisos.md` |
| Avisos | `10-avisos.md` |
| Notificaciones | `11-notificaciones.md` |
| Archivos | `12-archivos.md` |
