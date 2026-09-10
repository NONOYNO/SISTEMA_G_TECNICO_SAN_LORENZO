# Archivos

Almacenamiento en `storage/uploads/` (fuera de `public/`).

- **Múltiples archivos** por aviso (`attachments[]` con `multiple`)
- Límite por archivo: `UPLOAD_MAX_MB` (por defecto **512 MB**, permite >200 MB)
- Extensiones permitidas: pdf, office, txt, imágenes, zip
- Prohibidas: php, exe, bat, sh, cgi, etc.
- Validación MIME, tamaño, nombres aleatorios
- Descarga autenticada: `GET /files/{id}/download`

## PHP / XAMPP

Para archivos grandes, en `C:\xampp\php\php.ini` (y reiniciar Apache):

```ini
upload_max_filesize = 512M
post_max_size = 600M
max_file_uploads = 50
max_execution_time = 600
memory_limit = 768M
```

También hay `app/public/.user.ini` y directivas en `.htaccess`.
