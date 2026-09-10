# Configuración MySQL

- Host: `127.0.0.1`
- Puerto: `3306`
- Base: `ue_san_lorenzo`
- Usuario típico XAMPP: `root` (sin contraseña)

Variables en `.env`:

```
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ue_san_lorenzo
DB_USERNAME=root
DB_PASSWORD=
```

Crear BD (automático con migraciones) o manualmente en phpMyAdmin con collation `utf8mb4_unicode_ci`.
