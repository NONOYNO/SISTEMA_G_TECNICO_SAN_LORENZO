# Despliegue (portable — sin depender del nombre de carpeta)

## Recomendado en producción

Apunte el **DocumentRoot** del VirtualHost / hosting a:

```text
…/app/public
```

Así la app vive en la raíz del sitio (`https://tudominio.edu.ec/login`) sin carpeta fija.

1. Apache + MySQL activos  
2. Copiar el proyecto a cualquier ruta del servidor  
3. `.env`:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_KEY` fuerte y único
   - **`APP_URL=` vacío** (auto-detección) **o** la URL pública exacta del sitio  
4. `php database/migrate.php` y `php database/seed.php`  
5. Escritura en `storage/`, `logs/`, `uploads/`  
6. Verificar `/login`

## XAMPP / subcarpeta

Si no usa VirtualHost y deja el proyecto bajo `htdocs/cualquier-nombre/`:

- Entrar por `http://localhost/cualquier-nombre/` (redirige a `app/public/`)
- O `http://localhost/cualquier-nombre/app/public/login`
- Con `APP_URL` vacío, enlaces y router usan la carpeta real detectada automáticamente

## Notas

- No hardcodear nombres tipo `SISTEMA_G_…` en URLs.
- Assets se sirven vía `/assets/...` desde el front controller.
- Backup: dump MySQL + `storage/uploads`.
