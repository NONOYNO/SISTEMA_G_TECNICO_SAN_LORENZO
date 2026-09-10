---
name: deployment
description: Despliega y configura PortalInfor en XAMPP (Apache, MySQL, .env, permisos, hardenización local). Usar al instalar el proyecto, preparar demo institucional o publicar en entorno controlado.
---

# Despliegue XAMPP — PortalInfor

## Nombre

`deployment`

## Propósito

Instalar, configurar y publicar el portal en entorno XAMPP (Apache + MySQL/MariaDB + PHP 8) de forma repetible: document root, `.env`, migraciones, seeders, permisos de carpetas, hardenización básica y checklist de puesta en marcha para la Unidad Educativa Fiscomisional San Lorenzo.

## Cuándo usarla

- Al clonar/copiar el proyecto a `htdocs`.
- Al preparar una demo para autoridades.
- Al configurar un nuevo PC de desarrollo.
- Antes de entregar el sistema a un entorno institucional controlado.
- Tras cambios mayores de configuración Apache/PHP.

## SPEC relacionada

- `spec/24-despliegue-xampp.md`
- `spec/18-migraciones.md`
- `spec/19-seeders.md`
- `spec/05-seguridad.md`
- `spec/23-backup.md`

## Pasos de implementación

1. Leer `spec/24-despliegue-xampp.md` y README.
2. Verificar XAMPP: Apache + MySQL activos; PHP 8.x; extensiones `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`.
3. Ubicar proyecto (ej. `C:\xampp\htdocs\PortalInfor`).
4. Apuntar DocumentRoot / Alias al front controller `app/public` (o la carpeta `public` definida), no a todo el código.
5. Copiar `.env.example` → `.env` y configurar DB, `APP_URL`, zona horaria `America/Guayaquil`.
6. Crear base de datos MySQL vacía.
7. Ejecutar migraciones.
8. Ejecutar seeders solo en desarrollo/demo.
9. Crear/verificar carpetas escribibles: `storage/`, `uploads/`, `logs/` con permisos adecuados.
10. Proteger `.env`, `skills/`, `spec/`, `database/` de acceso web directo (Apache rules).
11. Probar URL base: login con credenciales demo.
12. Configurar backup de BD y de `uploads/` (SPEC 23).
13. Documentar URL local, versión PHP y pasos en `docs/` o README.
14. Para “producción” institucional: desactivar seeders, `display_errors=Off`, HTTPS si está disponible, credenciales reales fuera de `credenciales.md`.

## Convenciones de código

- URL local típica: `http://localhost/PortalInfor/public/` (ajustar a virtual host).
- Config solo vía `.env` + `app/config`.
- No desplegar con debug abierto.
- Versionar `CHANGELOG.md` en cada entrega.
- Scripts de migrate/seed por CLI, no por browser.
- Mantener `.gitignore` con `.env`, logs, uploads de usuario, vendor si aplica.

### Checklist rápido de arranque

```text
1. Apache + MySQL ON
2. .env configurado
3. migrate
4. seed (solo demo)
5. abrir login
6. verificar 4 roles
```

## Checklist de seguridad

- [ ] Document root limitado a `public/`.
- [ ] `.env` no accesible por HTTP.
- [ ] `display_errors` off fuera de local.
- [ ] Uploads no ejecutables.
- [ ] Usuario MySQL con privilegios mínimos.
- [ ] Credenciales demo no usadas en producción.
- [ ] HTTPS/headers cuando el entorno lo permita.
- [ ] Backups programados o procedimiento documentado.

## Checklist de pruebas

- [ ] Home/login carga por la URL documentada.
- [ ] Assets CSS/JS/Bootstrap cargan (sin 404).
- [ ] Login de cuentas seeder OK (demo).
- [ ] Escritura en `uploads/` y `logs/` OK.
- [ ] Rutas protegidas redirigen sin sesión.
- [ ] phpMyAdmin solo para admin local.
- [ ] Reinicio de Apache mantiene la app operativa.
- [ ] Procedimiento de backup/restore probado al menos una vez.

## Errores comunes a evitar

- Servir todo `PortalInfor/` como document root.
- Olvidar extensiones PHP (`fileinfo` para uploads).
- Dejar seeders y debug en entrega final.
- Rutas hardcodeadas a `C:\xampp\...` en código.
- No crear la BD antes de migrar.
- Mezclar proyectos en la misma BD sin prefijos/nombre claro.
- No documentar la URL exacta para usuarios no técnicos.

## Criterio de done

Despliegue está **done** cuando la app abre por la URL documentada en XAMPP, `.env` y BD están configurados, migraciones (y seeders de demo si aplica) corren, carpetas sensibles están protegidas, login funciona, y existe procedimiento claro de backup y hardenización según SPEC 24/23/05.
