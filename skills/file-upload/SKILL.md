---
name: file-upload
description: Gestiona subida, almacenamiento y descarga segura de archivos en SISTEMA_G_TECNICO_SAN_LORENZO (PDF/Office, validación MIME, permisos). Usar al adjuntar archivos a avisos/notificaciones o exponer downloads.
---

# Carga de Archivos — SISTEMA_G_TECNICO_SAN_LORENZO

## Nombre

`file-upload`

## Propósito

Permitir adjuntar, almacenar y descargar archivos institucionales de forma segura (PDF, Office, imágenes, TXT, ZIP según SPEC), con validación de tipo/tamaño, nombres seguros, control de acceso y registro de auditoría.

## Cuándo usarla

- Al añadir adjuntos a avisos o notificaciones.
- Al implementar endpoints de upload/download/delete.
- Al endurecer validaciones MIME/extensión.
- Al revisar almacenamiento en `uploads/` o `storage/`.
- Ante incidentes de archivo malicioso o descarga no autorizada.

## SPEC relacionada

- `spec/12-archivos.md`
- `spec/10-avisos.md`
- `spec/11-notificaciones.md`
- `spec/05-seguridad.md`
- `spec/21-owasp-top10.md`

## Pasos de implementación

1. Leer `spec/12-archivos.md` (tipos, tamaños, actores).
2. Migración `files` / `attachments`: nombre original, stored_name, mime, size, path, uploader_id, entity polimórfica o FK.
3. Configurar directorio `uploads/` fuera de ejecución PHP directa; servir vía controller.
4. Whitelist de extensiones: `pdf, doc, docx, xls, xlsx, ppt, pptx, txt, jpg, jpeg, png, gif, zip` (ajustar a SPEC).
5. Validar tamaño máximo (ej. configurable en `.env`).
6. Validar MIME real (finfo) además de extensión.
7. Renombrar a nombre aleatorio seguro (`bin2hex(random_bytes(16))` + ext permitida).
8. Permisos: `files.upload`, `files.download`, `files.delete`.
9. Download: stream con headers correctos; verificar authz sobre el recurso padre.
10. Delete: borrar registro + archivo físico (transacción lógica); audit.
11. Bloquear ejecución: `.htaccess` / no PHP en uploads.

## Convenciones de código

- Nunca usar el nombre original como path en disco.
- Guardar `original_name` solo para UI/download filename.
- Service `FileUploadService` centraliza validación.
- Paths relativos en BD; root absoluto en config.
- Respuestas de error claras (“Tipo no permitido”, “Archivo demasiado grande”).
- Imágenes de UI institucional pueden vivir en `assets/`; uploads de usuarios en `uploads/`.
- Virus scan: documentar como mejora si no está en alcance XAMPP local.

## Checklist de seguridad

- [ ] Whitelist de extensiones y MIME.
- [ ] Límite de tamaño.
- [ ] Nombre de archivo sanitizado/aleatorio.
- [ ] Uploads no ejecutables (sin `.php`, `.phtml`, doble extensión).
- [ ] Download autenticado y autorizado.
- [ ] Path traversal bloqueado (`../`).
- [ ] CSRF en upload.
- [ ] Audit upload/download/delete.
- [ ] No listar directorio uploads vía Apache.

## Checklist de pruebas

- [ ] Subir PDF válido OK.
- [ ] Rechazar `.php` / `.exe` / doble extensión.
- [ ] Rechazar archivo > límite.
- [ ] MIME spoofing básico rechazado cuando sea detectable.
- [ ] Descarga con permiso OK; sin permiso 403.
- [ ] Eliminar quita archivo y registro.
- [ ] Nombre original con caracteres raros no rompe headers.
- [ ] Adjunto visible en card de aviso/notificación.

## Errores comunes a evitar

- Guardar uploads dentro de `public/` con ejecución habilitada.
- Confiar solo en `$_FILES['type']`.
- Usar nombre de usuario en el path sin sanitizar.
- Enlaces directos permanentes sin control de acceso.
- No borrar archivo físico al eliminar registro.
- Permitir ZIP/exe sin regla de negocio.
- Exponer rutas absolutas del servidor en JSON.

## Criterio de done

File-upload está **done** cuando upload/download/delete cumplen whitelist y tamaños de la SPEC, los archivos no son ejecutables, el acceso está autorizado, hay auditoría, la integración con avisos/notificaciones funciona, y las pruebas de tipos inválidos fallan de forma segura.
