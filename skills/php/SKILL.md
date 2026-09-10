---
name: php
description: Estándares de PHP 8 para SISTEMA_G_TECNICO_SAN_LORENZO en XAMPP (tipado, PDO, sesiones, errores, PSR práctico). Usar al escribir o revisar código backend PHP, helpers, middleware, servicios o endpoints AJAX.
---

# PHP 8 — SISTEMA_G_TECNICO_SAN_LORENZO

## Nombre

`php`

## Propósito

Definir cómo escribir, organizar y validar código PHP 8 en el portal de la Unidad Educativa Fiscomisional San Lorenzo: estilo, tipado, manejo de errores, sesiones, PDO y compatibilidad con XAMPP/Apache.

## Cuándo usarla

- Al crear o modificar cualquier archivo `.php` de producción.
- Al implementar controllers, models, services, middleware, validators o helpers.
- Al corregir errores de runtime, deprecations o tipado.
- Al revisar calidad de código backend antes de pruebas.

## SPEC relacionada

- `spec/01-arquitectura.md`
- `spec/03-requisitos-no-funcionales.md`
- `spec/05-seguridad.md`
- `spec/00-master-spec.md`

## Pasos de implementación

1. Confirmar versión PHP 8.x en XAMPP (`php -v`).
2. Leer SPEC del módulo a implementar y esta SKILL.
3. Crear clase/archivo con `declare(strict_types=1);` y namespace correcto.
4. Tipar parámetros y retornos; usar `?Type` o union types cuando aplique.
5. Inyectar dependencias (PDO, repositorios, servicios) en constructores, no vía globales.
6. Validar entrada con validators dedicados antes de persistir.
7. Manejar excepciones con try/catch en bordes (controllers/ajax); registrar en `logs/`.
8. Devolver vistas o JSON coherente; nunca volcar `var_dump` en producción.
9. Ejecutar lint/prueba manual en XAMPP y actualizar SPEC.

## Convenciones de código

- `declare(strict_types=1);` al inicio de archivos de clases.
- Clases en PascalCase; métodos y variables en camelCase; constantes en UPPER_SNAKE.
- Un archivo = una clase principal.
- Preferir `final` en clases de servicio no extensibles.
- Arrays asociativos tipados vía DTOs o arrays documentados cuando no haya DTO.
- Usar `password_hash()` / `password_verify()` para contraseñas.
- Fechas en UTC o zona configurada (`America/Guayaquil`) de forma consistente.
- No usar `extract()`, `eval()`, `create_function()`, `mysql_*`.
- Respuestas AJAX: `Content-Type: application/json` y estructura `{ success, message, data, errors }`.
- Mensajes al usuario en español claro e institucional.

## Checklist de seguridad

- [ ] Entrada sanitizada/validada en servidor.
- [ ] Salida escapada en vistas (`htmlspecialchars` o helper).
- [ ] Consultas solo con PDO prepared statements.
- [ ] Sesiones con `session_regenerate_id(true)` post-login.
- [ ] Cookies de sesión: `HttpOnly`, `SameSite`, `Secure` cuando HTTPS.
- [ ] Sin secretos hardcodeados.
- [ ] `display_errors` desactivado fuera de local.
- [ ] Uploads validados por MIME/extensión/tamaño (ver skill `file-upload`).

## Checklist de pruebas

- [ ] Código corre en PHP 8 sin warnings/deprecations.
- [ ] Tipado falla correctamente ante tipos inválidos.
- [ ] Errores se registran en `logs/` sin filtrar datos sensibles.
- [ ] Endpoints responden códigos HTTP apropiados (200/400/401/403/404/422/500).
- [ ] Autoload/namespaces resuelven clases.
- [ ] Casos nulos y vacíos manejados.

## Errores comunes a evitar

- Mezclar estilos PHP 5 (`mysql_query`, variables no tipadas globales).
- Suprimir errores con `@`.
- Confiar solo en validación JavaScript.
- Concatenar SQL o HTML con datos crudos.
- Usar `md5`/`sha1` para contraseñas.
- Dejar `phpinfo()` o scripts de debug en producción.
- Dependencias circulares entre services y controllers.

## Criterio de done

El trabajo PHP está **done** cuando el código tipado en PHP 8 cumple convenciones del proyecto, usa PDO/sesiones seguras, maneja errores sin filtrar secretos, pasa pruebas del módulo y la SPEC refleja el estado implementado.
