# Despliegue en XAMPP

1. Apache y MySQL activos
2. Proyecto en `C:\xampp\htdocs\PortalInfor`
3. `.env` con `APP_ENV=production`, `APP_DEBUG=false` y `APP_KEY` fuerte
4. `php database/migrate.php` y `php database/seed.php`
5. Permisos de escritura en `storage/`, `logs/`, `uploads/`
6. Verificar acceso: `http://localhost/PortalInfor/app/public/login`

Backup recomendado: dump MySQL + carpeta `storage/uploads`.
