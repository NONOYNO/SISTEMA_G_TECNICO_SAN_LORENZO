# SPEC 24 — Despliegue en XAMPP

**Proyecto:** Unidad Educativa Fiscomisional San Lorenzo  
**Stack:** PHP 8 + MySQL + PDO + MVC + Bootstrap 5 + AJAX + XAMPP  
**Módulo:** Instalación y puesta en marcha local  
**Estado:** PENDING

---

## 1. Objetivo

Documentar los pasos concretos para desplegar el portal en XAMPP sobre Windows en `C:\xampp\htdocs\PortalInfor`, dejando Apache, MySQL y la aplicación operativos de forma segura para desarrollo.

## 2. Alcance

**Incluye:**
- Requisitos de software XAMPP.
- Configuración Apache (DocumentRoot / alias / rewrite).
- Creación de base MySQL y usuario.
- Variables `.env`.
- Permisos de carpetas `storage`, `uploads`, `logs`.
- Ejecución de migraciones y seeders.
- Verificación de URL local.
- Hardening básico para entorno local.

**Excluye:**
- Despliegue en hosting compartido/VPS Linux (solo notas).
- Dominio público y TLS productivo completo (guía mínima opcional).

## 3. Actores

| Actor | Tarea |
|-------|-------|
| Desarrollador | Instala y configura XAMPP |
| ADMIN seed | Valida login post-despliegue |

## 4. Requisitos

### Funcionales de despliegue
- RF-X01: Código ubicado en `C:\xampp\htdocs\PortalInfor`.
- RF-X02: Apache y MySQL iniciados desde XAMPP Control Panel.
- RF-X03: PHP 8.x activo (`php -v`).
- RF-X04: Extensiones: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `json`.
- RF-X05: `mod_rewrite` habilitado si se usan URLs amigables.
- RF-X06: DB `ue_san_lorenzo` creada.
- RF-X07: `.env` configurado desde `.env.example`.
- RF-X08: Migraciones + seeders ejecutados.
- RF-X09: App accesible en `http://localhost/PortalInfor/` (o vhost documentado).

### No funcionales
- RNF-X01: Tiempo de setup < 30 min en máquina estándar.
- RNF-X02: Documentación reproducible paso a paso.

## 5. Flujo funcional (pasos de despliegue)

```text
1. Instalar XAMPP (PHP 8+) en C:\xampp
2. Clonar/copiar proyecto a C:\xampp\htdocs\PortalInfor
3. Iniciar Apache + MySQL
4. Crear DB en phpMyAdmin: ue_san_lorenzo (utf8mb4)
5. Copiar .env.example → .env y editar:
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ue_san_lorenzo
   DB_USERNAME=root
   DB_PASSWORD= (vacío por defecto XAMPP o la configurada)
   APP_URL=http://localhost/PortalInfor
   APP_ENV=local
6. Crear carpetas writables: storage/, uploads/, logs/, storage/backups/
7. Configurar Apache:
   - AllowOverride All en htdocs (para .htaccess)
   - Deny acceso a /database, /storage, /logs, /.env
8. php database/migrate.php
9. php database/seed.php
10. Abrir navegador → login con credenciales seed
11. Verificar dashboard por rol
```

### VirtualHost opcional
```apache
<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/PortalInfor/app/public"
    ServerName uesanlorenzo.local
    <Directory "C:/xampp/htdocs/PortalInfor/app/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```
Agregar en `C:\Windows\System32\drivers\etc\hosts`: `127.0.0.1 uesanlorenzo.local`

## 6. Reglas de negocio

- RN-X01: DocumentRoot recomendado = `app/public` (no exponer todo el repo).
- RN-X02: Si se usa `htdocs/PortalInfor` sin public, proteger rutas sensibles con `.htaccess`.
- RN-X03: `APP_DEBUG=true` solo en local.
- RN-X04: No versionar `.env`.
- RN-X05: Puerto 80/443 libres; si ocupados, ajustar Apache.
- RN-X06: Tras seed, validar los 4 usuarios `@uesanlorenzo.edu`.

## 7. Estructura de datos

### Config mínima `.env`
```text
APP_NAME="UE San Lorenzo"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost/PortalInfor
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ue_san_lorenzo
DB_USERNAME=root
DB_PASSWORD=
SESSION_LIFETIME=120
MIGRATIONS_WEB=false
BACKUP_WEB=false
```

### Base
```text
Nombre: ue_san_lorenzo
Charset: utf8mb4
Collation: utf8mb4_unicode_ci
```

## 8. Validaciones post-install

- [ ] `http://localhost/PortalInfor` responde 200.
- [ ] `php -m` muestra `pdo_mysql`.
- [ ] Login `admin@uesanlorenzo.edu` / `Admin123!` OK.
- [ ] Escritura en `storage/` y `uploads/` OK.
- [ ] `/database` no listable.
- [ ] `.env` no descargable.
- [ ] migrate status = all applied.

## 9. Seguridad

- Deshabilitar índices de directorio.
- Bloquear acceso HTTP a carpetas sensibles.
- `display_errors` off si se simula staging.
- Usuario MySQL con privilegios mínimos en entornos no locales.
- Firewall Windows: no exponer MySQL 3306 a la red sin necesidad.
- Cumplir checklist A05 de `spec/21-owasp-top10.md`.

## 10. Interfaz

- No UI de instalación wizard obligatoria (fase 1): README + este SPEC.
- Opcional futuro: instalador web con token local.

## 11. AJAX requerido

No requerido para el despliegue en sí. Tras desplegar, verificar que endpoints AJAX respondan con sesión (smoke test):

| Smoke | Esperado |
|-------|----------|
| GET `/ajax/dashboard/metrics` autenticado | 200 JSON |
| GET mismo sin sesión | 401 |

## 12. Respuestas esperadas

**Éxito de instalación:**
- Página de login institucional visible.
- Seed users operativos.
- Sin errores fatales PHP en `logs/`.

**Fallo típico DB:**
```text
SQLSTATE[HY000] [1045] Access denied
→ Revisar DB_PASSWORD en .env / usuario MySQL
```

**Fallo rewrite:**
```text
404 en rutas amigables
→ Habilitar mod_rewrite + AllowOverride All
```

## 13. Manejo de errores

| Problema | Acción |
|----------|--------|
| Puerto 80 ocupado | Cambiar Listen 8080 o cerrar IIS/Skype |
| MySQL no arranca | Revisar log `C:\xampp\mysql\data\*.err` |
| Permisos escritura | Revisar ACLs carpeta storage |
| Página en blanco | Activar log PHP temporalmente en local |
| Charset raro | Verificar utf8mb4 en DB y PDO |

## 14. Casos de prueba

| ID | Caso | Esperado |
|----|------|----------|
| DEP-001 | Start Apache/MySQL | Servicios running |
| DEP-002 | Crear DB | OK en phpMyAdmin |
| DEP-003 | migrate | Tablas creadas |
| DEP-004 | seed | 4 users |
| DEP-005 | Login 4 roles | Dashboards correctos |
| DEP-006 | Bloqueo /.env | 403/404 |
| DEP-007 | Upload prueba VICERRECTOR | Archivo en storage |
| DEP-008 | AJAX metrics | 200 |
| DEP-009 | Reinicio PC + servicios | App sigue OK |
| DEP-010 | APP_DEBUG=false smoke | Sin stack traces |

## 15. Criterios de aceptación

- [ ] Proyecto corre desde `C:\xampp\htdocs\PortalInfor`.
- [ ] Apache + MySQL + PHP 8 operativos.
- [ ] Migraciones y seeders aplicados.
- [ ] Logins de prueba funcionan.
- [ ] Carpetas sensibles protegidas.
- [ ] Documentación de restore/backup alineada (`spec/23`).

## 16. Dependencias

- `spec/01-arquitectura.md`, `spec/18-migraciones.md`, `spec/19-seeders.md`, `spec/23-backup.md`, `spec/21-owasp-top10.md`
- Skills: `deployment`, `php`, `mysql`

## 17. SKILL requerida

```text
skills/deployment/SKILL.md
```

## 18. Estado de implementación

**PENDING**
