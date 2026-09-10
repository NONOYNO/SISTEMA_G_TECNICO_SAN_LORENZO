# Arquitectura

## Patrón

MVC en PHP 8 con PDO, Bootstrap 5 y rutas propias.

```text
HTTP → app/public/index.php → Router → Middleware → Controller → Service → Repository → MySQL
                                                              ↓
                                                         View / JSON
```

## Capas

| Capa | Ubicación | Responsabilidad |
|------|-----------|-----------------|
| Front controller | `app/public/` | Entrada HTTP |
| Router | `app/Router.php` | Rutas + middleware |
| Controllers | `app/Controllers/` | Orquestación HTTP |
| Services | `app/Services/` | Reglas de negocio |
| Repositories | `app/Repositories/` | Persistencia PDO |
| Views | `app/Views/` | UI Bootstrap |
| Spec / Skills | `spec/`, `skills/` | POLKDEV |

## Seguridad

- Sesiones regeneradas en login
- CSRF en POST
- RBAC por permisos granulares
- Passwords con `password_hash`
- Uploads fuera de `public/` en `storage/uploads/`
