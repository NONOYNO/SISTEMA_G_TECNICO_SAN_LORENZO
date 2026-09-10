# Migraciones

```bash
php database/migrate.php
```

Archivos en `database/migrations/` con naming `YYYYMMDDHHMMSS_descripcion.php`.

El runner crea la BD si no existe, registra migraciones aplicadas en la tabla `migrations` y no reaplica las ya ejecutadas.
