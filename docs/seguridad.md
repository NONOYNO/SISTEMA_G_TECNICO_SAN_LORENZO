# Seguridad

## Controles implementados

- Autenticación con bloqueo tras 5 intentos fallidos (15 min)
- CSRF (`_token` / header `X-CSRF-TOKEN`)
- Escape de salida HTML (`e()`)
- PDO prepared statements
- Middleware `auth`, `guest`, `permission:*`, `csrf`
- Headers: `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`
- Auditoría de login y operaciones sensibles
- Validación de extensión/MIME/tamaño en archivos
- Nombres de archivo renombrados (aleatorios)

## OWASP Top 10

Ver `spec/21-owasp-top10.md` para mitigaciones A01–A10.
