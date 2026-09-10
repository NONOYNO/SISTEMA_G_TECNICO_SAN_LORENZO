# SISTEMA_G_TECNICO_SAN_LORENZO — UE Fiscomisional San Lorenzo

Portal informativo-administrativo de la **Unidad Educativa Fiscomisional San Lorenzo**.

Stack: **PHP 8** · **MySQL (PDO)** · **MVC** · **Bootstrap 5** · **AJAX** · **XAMPP**

Metodología: **SPEC → SKILL → CODE** (POLKDEV)

---

## Requisitos

- XAMPP con PHP 8.x, Apache y MySQL/MariaDB
- Extensiones PHP: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `json`
- `mod_rewrite` habilitado (`AllowOverride All` en `htdocs`)

---

## Instalación (XAMPP)

1. Copiar el proyecto en **cualquier** carpeta bajo `htdocs` (el nombre no importa)
2. Iniciar **Apache** y **MySQL** desde el XAMPP Control Panel
3. Crear la base de datos en phpMyAdmin:
   - Nombre: `ue_san_lorenzo`
   - Collation: `utf8mb4_unicode_ci`
4. Copiar entorno:
   ```bash
   copy .env.example .env
   ```
5. Ajustar `.env`: `DB_*`, `APP_KEY`. Dejar **`APP_URL` vacío** para auto-detección (recomendado)
6. Ejecutar migraciones y seeders:
   ```bash
   php database/migrate.php
   php database/seed.php
   ```
7. Pruebas rápidas:
   ```bash
   php tests/run.php
   ```
8. Abrir en el navegador (sustituya `SU_CARPETA` por el nombre real):
   - `http://localhost/SU_CARPETA/` → redirige a `app/public/`
   - Login: `http://localhost/SU_CARPETA/app/public/login`

### Producción

DocumentRoot → `app/public`. Con `APP_URL` vacío, la navegación usa la raíz del despliegue sin carpeta hardcodeada. Ver [`docs/despliegue.md`](docs/despliegue.md).

---

## Credenciales de prueba

Ver [`credenciales.md`](credenciales.md). Resumen:

| Rol          | Correo                         | Contraseña       |
|--------------|--------------------------------|------------------|
| ADMIN        | admin@uesanlorenzo.edu        | Admin123!        |
| RECTOR       | rector@uesanlorenzo.edu       | Rector123!       |
| VICERRECTOR  | vicerrector@uesanlorenzo.edu  | Vicerrector123!  |
| DOCENTE      | docente@uesanlorenzo.edu      | Docente123!      |

Solo para desarrollo. Nunca usar en producción.

---

## Arquitectura

```text
HTTP → Apache (app/public/.htaccess)
     → app/public/index.php
     → bootstrap (env, autoload, sesión)
     → Router + Middleware
     → Controller → Service → Repository/Model (PDO)
     → View (Bootstrap) | JSON AJAX
```

### Carpetas principales

```text
SISTEMA_G_TECNICO_SAN_LORENZO/
├── app/
│   ├── bootstrap.php
│   ├── Router.php
│   ├── config/
│   ├── Controllers/
│   ├── Models/
│   ├── Services/
│   ├── Repositories/
│   ├── Middleware/
│   ├── Validators/
│   ├── helpers/
│   ├── routes/
│   ├── views/
│   ├── assets/
│   └── public/          # Document root / front controller
├── database/
│   ├── migrations/
│   └── seeders/
├── storage/             # privado
├── uploads/             # privado (descarga autenticada)
├── logs/
├── spec/
├── skills/
└── tests/
```

### Capas

| Capa          | Rol                                      |
|---------------|------------------------------------------|
| Controllers   | HTTP, orquestación                       |
| Services      | Reglas de negocio                        |
| Repositories  | SQL PDO                                  |
| Models        | Entidades                                |
| Middleware    | Auth, CSRF, permisos                     |
| Views         | Presentación Bootstrap 5                 |
| Helpers       | Utilidades (env, CSRF, auth, escape)     |

### Namespaces (PSR-4 estilo)

`App\` → `app/` (`App\Controllers`, `App\Models`, `App\Services`, `App\Repositories`, `App\Middleware`, `App\Validators`, `App\Helpers`)

---

## Paleta institucional

- Celeste `#4FC3F7`
- Azul `#1565C0`
- Blanco `#FFFFFF`

---

## Seguridad básica del núcleo

- Sesión con cookies `HttpOnly` / `SameSite=Lax`
- CSRF en POST (`csrf_token`, middleware, header `X-CSRF-TOKEN` en AJAX)
- Escape de salida (`e()`)
- Secretos solo en `.env` (ignorado por git)
- Uploads/logs fuera de ejecución directa de lógica sensible

---

## Desarrollo

Documentación funcional: carpeta `spec/`.  
Convenciones de implementación: carpeta `skills/`.

Cambios relevantes: [`CHANGELOG.md`](CHANGELOG.md).
