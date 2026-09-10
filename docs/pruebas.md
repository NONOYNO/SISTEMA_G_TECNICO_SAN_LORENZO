# Pruebas

## Cómo ejecutar

```bash
php tests/run.php
```

## Cobertura mínima

- Login válido / inválido
- Hash de contraseña
- CSRF token presente
- RBAC: permisos ADMIN vs DOCENTE
- Extensiones de archivo permitidas/prohibidas

## Matriz manual

| Caso | Rol | Resultado esperado |
|------|-----|--------------------|
| Login admin | ADMIN | Dashboard con métricas |
| Crear aviso | VICERRECTOR | Aviso + notificaciones |
| Ver usuarios | DOCENTE | 403 |
| Descargar adjunto | DOCENTE | OK si published y permiso |
