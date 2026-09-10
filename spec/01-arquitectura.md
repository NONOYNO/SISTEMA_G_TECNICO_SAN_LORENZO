# SPEC 01 — Arquitectura MVC SISTEMA_G_TECNICO_SAN_LORENZO

**Proyecto:** Unidad Educativa Fiscomisional San Lorenzo (SISTEMA_G_TECNICO_SAN_LORENZO)  
**Metodología:** POLKDEV — SPEC → SKILL → CODE  
**Estado de implementación:** PENDING

---

## 1. Objetivo

Definir la arquitectura MVC del sistema, la organización de carpetas bajo `app/`, las responsabilidades por capa y el flujo completo de una petición HTTP (HTML y AJAX) desde el front controller hasta la respuesta.

## 2. Alcance

### Incluye
- Patrón MVC + capas de soporte (middleware, services, repositories, helpers, validators).
- Estructura de directorios obligatoria.
- Front controller en `public/index.php`.
- Enrutamiento y resolución de controladores.
- Separación de assets públicos vs storage privado.
- Convención de nombres y namespaces PSR-4 (recomendado).

### No incluye
- Microservicios o colas externas.
- Framework completo tipo Laravel (se permite inspiración de patrones, no dependencia obligatoria).

## 3. Actores

| Actor | Interacción con la arquitectura |
|-------|----------------------------------|
| Desarrollador / Agente IA | Implementa capas según esta SPEC y skills |
| Usuario final | Consume vistas y endpoints AJAX |
| Apache (XAMPP) | Sirve `public/` y reescribe a `index.php` |

## 4. Requisitos

1. Todo el código de aplicación vive bajo `app/` (salvo `public/`, `database/`, `storage/`, `uploads/`, `logs/`).
2. El document root de Apache debe apuntar a `public/` (nunca a la raíz del proyecto).
3. Controllers delgados; lógica de negocio en Services; acceso a datos en Repositories/Models.
4. Middleware para auth, CSRF, permisos y rate-limit básico de login.
5. Views solo presentan datos; sin SQL ni lógica de negocio.
6. Endpoints AJAX en rutas dedicadas (`/ajax/...` o controladores con `Accept: application/json`).
7. Configuración sensible vía `.env` (no commitear secretos).

## 5. Flujo funcional

### Flujo request estándar

```text
Cliente HTTP
  → Apache (public/.htaccess)
    → public/index.php (bootstrap)
      → Carga config, autoload, sesión
      → Router::dispatch(method, uri)
        → Middleware stack (Session, CSRF, Auth, Permission)
          → Controller@action
            → Validator (si mutación)
            → Service
              → Repository / Model (PDO)
            → View::render(...)  |  JsonResponse::send(...)
```

### Flujo AJAX

```text
JS (fetch/XHR) + header X-CSRF-TOKEN / token en body
  → misma ruta o /ajax/{recurso}
  → Controller responde JSON { success, message, data, errors }
  → UI actualiza DOM (cards, tablas, toasts)
```

## 6. Reglas de negocio

1. Ningún archivo PHP ejecutable debe residir en `uploads/` o `storage/` accesible por URL directa.
2. `public/` solo contiene `index.php`, `.htaccess`, assets estáticos y entrypoints públicos.
3. Un controller no instancia PDO directamente; usa contenedor simple o factory de conexión.
4. Nombres de clases: `UserController`, `UserService`, `UserRepository`, `User` (model).
5. Rutas nombradas preferibles para links y redirects.
6. Errores 404/403/500 tienen vistas dedicadas + JSON equivalente.

## 7. Estructura de datos

No aplica esquema de BD aquí; estructura de carpetas:

```text
SISTEMA_G_TECNICO_SAN_LORENZO/
├── app/
│   ├── Controllers/
│   ├── Models/
│   ├── Views/
│   │   ├── layouts/
│   │   ├── auth/
│   │   ├── users/
│   │   ├── announcements/
│   │   ├── notifications/
│   │   ├── partials/
│   │   └── errors/
│   ├── Middleware/
│   ├── Services/
│   ├── Repositories/
│   ├── Helpers/
│   ├── Validators/
│   ├── Routes/
│   │   ├── web.php
│   │   └── ajax.php
│   └── Core/                 # Router, Controller base, Request, Response, Session, Database
├── public/
│   ├── index.php
│   ├── .htaccess
│   └── assets/
│       ├── css/
│       ├── js/
│       └── img/
├── storage/                  # privado: logs internos, cache, archivos procesados
├── uploads/                  # privado: adjuntos (servidos vía controller)
├── database/
│   ├── migrations/
│   └── seeders/
├── logs/
├── spec/
├── skills/
├── tests/
├── .env.example
└── README.md
```

### Responsabilidades por capa

| Capa | Responsabilidad |
|------|-----------------|
| Controllers | HTTP in/out, orquestación, status codes |
| Models | Representación de entidad / mapping simple |
| Views | HTML/Bootstrap |
| Middleware | Cross-cutting: auth, CSRF, RBAC |
| Services | Casos de uso / reglas de negocio |
| Repositories | SQL PDO, queries |
| Helpers | funciones puras (str, date, url, escape) |
| Validators | reglas de validación reutilizables |
| Routes | mapa método+path → handler |
| ajax (Routes/Controllers) | contratos JSON |
| assets | CSS/JS/img públicos |
| public | único entrypoint web |

## 8. Validaciones

- Router: método HTTP permitido; ruta existente.
- Middleware Auth: sesión válida y usuario activo.
- Middleware Permission: permiso requerido en ruta.
- Request: tamaño máximo de body/upload según `php.ini` y config app.
- Bootstrap: fallar rápido si faltan variables `.env` críticas (`DB_*`, `APP_KEY`).

## 9. Seguridad

- Document root = `public/`.
- `.htaccess` bloquea listado de directorios.
- Secrets solo en entorno / `.env`.
- CSRF middleware en POST/PUT/PATCH/DELETE.
- Separación storage privado + descarga autenticada.
- Headers de seguridad aplicados en response bootstrap (ver SPEC 05).

## 10. Interfaz

- Layout base: `Views/layouts/app.php` (navbar + sidebar + content + footer).
- Layout auth: `Views/layouts/guest.php` (login/registro).
- Partials: alerts, pagination, CSRF input, flash messages.
- Assets versionados por query `?v=` o hash simple para cache bust.

## 11. AJAX requerido

| Endpoint patrón | Método | Uso |
|-----------------|--------|-----|
| `/ajax/ping` | GET | health check sesión |
| `/ajax/{module}/list` | GET | listados parciales |
| `/ajax/{module}/store` | POST | crear |
| `/ajax/{module}/update/{id}` | POST/PUT | actualizar |
| `/ajax/{module}/delete/{id}` | POST/DELETE | eliminar/desactivar |

Registro de rutas en `app/Routes/ajax.php`.

## 12. Respuestas esperadas

**HTML:** status 200 + layout renderizado.  
**JSON éxito:**

```json
{ "success": true, "message": "...", "data": { }, "errors": {} }
```

**JSON error validación (400):**

```json
{ "success": false, "message": "Datos inválidos", "data": null, "errors": { "email": ["El correo es obligatorio"] } }
```

## 13. Manejo de errores

| Capa | Estrategia |
|------|------------|
| Router | 404 view/JSON |
| Middleware | 401/403 |
| Validator | 400 + errors |
| Service/Repository | Exception tipada → Handler → 500 seguro + log |
| PHP fatal | `log` + página de error genérica |

## 14. Casos de prueba

| ID | Caso | Esperado |
|----|------|----------|
| AR-01 | GET ruta inexistente | 404 |
| AR-02 | POST sin CSRF | 419 |
| AR-03 | Acceso a `/uploads/file.pdf` directo | Denegado / 404 |
| AR-04 | Controller llama Service (no PDO) | Cumple arquitectura |
| AR-05 | AJAX list con sesión | 200 JSON |
| AR-06 | Document root mal configurado | Checklist despliegue falla |

## 15. Criterios de aceptación

- [ ] Estructura de carpetas creada según esta SPEC.
- [ ] Front controller único en `public/index.php`.
- [ ] Router despacha web y ajax.
- [ ] Middleware stack documentado e implementable.
- [ ] Capas con responsabilidades claras y sin fugas de SQL a Views.
- [ ] Flujo request documentado y reproducible.

## 16. Dependencias

- SPEC `00-master-spec.md`, `05-seguridad.md`, `06-ui-ux.md`.
- PHP 8, Apache rewrite module.
- Autoload Composer PSR-4 (recomendado) o autoload spl propio.

## 17. SKILL requerida

- `skills/architecture/SKILL.md`
- `skills/mvc/SKILL.md`
- `skills/php/SKILL.md`
- `skills/ajax/SKILL.md`

## 18. Estado de implementación

**PENDING**
