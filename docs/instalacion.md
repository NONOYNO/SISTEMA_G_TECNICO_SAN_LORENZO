# Instalación — PortalInfor

## Requisitos

- XAMPP (Apache + MySQL/MariaDB + PHP 8.x)
- Extensiones: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `json`
- `mod_rewrite` con `AllowOverride All` en `htdocs`

## Pasos

1. Ubicar el proyecto en `C:\xampp\htdocs\PortalInfor`
2. Iniciar Apache y MySQL en el panel de XAMPP
3. Copiar `.env.example` a `.env` y ajustar `DB_*`, `APP_URL`, `APP_KEY`
4. Desde la raíz del proyecto:

```bash
php database/migrate.php
php database/seed.php
```

5. Abrir [http://localhost/PortalInfor/](http://localhost/PortalInfor/)

## Credenciales

Ver `credenciales.md`.
