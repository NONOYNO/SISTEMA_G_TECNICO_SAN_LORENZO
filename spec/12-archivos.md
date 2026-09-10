# SPEC 12 — Archivos (Uploads / Downloads)

**Proyecto:** Unidad Educativa Fiscomisional San Lorenzo (PortalInfor)  
**Metodología:** POLKDEV — SPEC → SKILL → CODE  
**Estado de implementación:** PENDING

---

## 1. Objetivo

Especificar la gestión segura de archivos adjuntos: tipos permitidos y prohibidos, validación MIME, renombrado, almacenamiento fuera de `public/`, descarga autenticada y prevención de path traversal.

## 2. Alcance

### Incluye
- Upload vinculado a avisos, notificaciones u otras entidades.
- Whitelist de extensiones y blacklist de peligrosas.
- Validación MIME con `fileinfo`.
- Rename a nombre opaco.
- Storage en `storage/` o `uploads/` fuera del document root.
- Download vía controller.
- Eliminación de metadata + archivo con permiso.

### No incluye
- Antivirus gateway corporativo (recomendación futura).
- Edición online de documentos.
- Almacenamiento S3 (fase XAMPP local).

## 3. Actores

| Actor | Acciones |
|-------|----------|
| Usuarios con `files.upload` | Subir |
| Usuarios con `files.download` | Descargar autorizados |
| Usuarios con `files.delete` | Eliminar |
| DOCENTE típico | download de adjuntos de avisos/notif dirigidos |

## 4. Requisitos

### 4.1 Extensiones permitidas (whitelist)
`pdf`, `doc`, `docx`, `xls`, `xlsx`, `ppt`, `pptx`, `txt`, `jpg`, `jpeg`, `png`, `zip`

### 4.2 Extensiones / tipos prohibidos (ejemplos no exhaustivos)
`php`, `phtml`, `php3`, `php4`, `php5`, `phar`, `exe`, `bat`, `cmd`, `com`, `msi`, `sh`, `bash`, `cgi`, `js` (si no se requiere), `html`, `htm`, `shtml`, `svg` (opcional bloquear por XSS), `dll`, `sys`, `ps1`, `vbs`, `jar`

### 4.3 Controles técnicos
1. Validar extensión final contra whitelist (case-insensitive).
2. Rechazar doble extensión peligrosa (`archivo.php.pdf` solo ok si política exige MIME pdf real; preferir rechazar si contiene `.php.` en nombre original).
3. MIME real vía `finfo_file` debe mapear a la extensión.
4. Tamaño ≤ `UPLOAD_MAX_MB`.
5. `stored_name` = hex/random + extensión segura.
6. Path: `uploads/YYYY/MM/{stored_name}` relativo a base privada.
7. Registrar fila en `attachments`.
8. Download: buscar por id, auth, permiso, ownership/acceso entidad, `readfile` con headers.
9. Bloquear `..`, null bytes, paths absolutos en cualquier input.
10. `.htaccess` en carpeta uploads: `Require all denied` (Apache 2.4).

## 5. Flujo funcional

### Upload
```text
1. POST multipart + CSRF + attachable_type/id
2. Permission files.upload
3. Validar tamaño, ext, MIME, nombre original sanitizado (solo para display)
4. Generar stored_name
5. Mover con move_uploaded_file a destino realpath dentro de base
6. Insert attachments (checksum opcional sha256)
7. Audit files.upload
8. JSON metadata (sin disk_path absoluto)
```

### Download
```text
1. GET /files/{id}/download
2. Auth + files.download + autorización de entidad
3. Resolver path interno
4. Verificar realpath starts with base_uploads
5. Stream file + Content-Type + Content-Disposition attachment
```

### Delete
```text
POST /ajax/files/{id}/delete
→ permiso + auth entidad → unlink si existe → delete row → audit
```

## 6. Reglas de negocio

1. Nunca servir uploads como estáticos públicos.
2. El cliente solo conoce `attachment.id` y `original_name`.
3. Al borrar entidad padre: política CASCADE adjuntos o huérfanos limpiables.
4. ZIP permitido pero no se inspecciona contenido interno en fase 1 (documentar riesgo residual).
5. Imágenes no se ejecutan; se descargan o se muestran solo vía endpoint seguro si se implementa preview.
6. Fallo de validación no deja archivos temporales en destino final.

## 7. Estructura de datos

Tabla `attachments` (SPEC 04).

**Respuesta metadata segura:**
```json
{
  "id": 55,
  "original_name": "circular.pdf",
  "mime_type": "application/pdf",
  "extension": "pdf",
  "size_bytes": 204800,
  "attachable_type": "announcement",
  "attachable_id": 15,
  "created_at": "2026-09-08 15:30:00"
}
```

**Mapa MIME sugerido (parcial):**

| Ext | MIME aceptados |
|-----|----------------|
| pdf | application/pdf |
| doc | application/msword |
| docx | application/vnd.openxmlformats-officedocument.wordprocessingml.document |
| xls | application/vnd.ms-excel |
| xlsx | application/vnd.openxmlformats-officedocument.spreadsheetml.sheet |
| ppt | application/vnd.ms-powerpoint |
| pptx | application/vnd.openxmlformats-officedocument.presentationml.presentation |
| txt | text/plain |
| jpg/jpeg | image/jpeg |
| png | image/png |
| zip | application/zip, application/x-zip-compressed |

## 8. Validaciones

| Check | Resultado si falla |
|-------|--------------------|
| No file | 400 |
| Ext no whitelist | 400 |
| Ext blacklist / php | 400 |
| MIME mismatch | 400 |
| Size exceed | 400 |
| attachable inexistente | 400/404 |
| No is_uploaded_file | 400 |
| realpath fuera base | 500/abort + log seguridad |

Nombre original: strip path, max 255, remover caracteres de control.

## 9. Seguridad

- Controles de SPEC 05 aplicados.
- Deshabilitar ejecución CGI/PHP en directorio storage.
- Headers download: `Content-Disposition: attachment` (evitar inline HTML).
- Rate limit opcional a uploads repetidos.
- Logs de rechazos (ext/MIME) sin guardar binario.

## 10. Interfaz

- Input file Bootstrap en formularios de avisos/notif.
- Lista de adjuntos con ícono por tipo + tamaño humanizado.
- Botón descargar / eliminar (si permiso).
- Progress bar opcional (AJAX XHR upload).
- Mensajes claros: “Tipo de archivo no permitido”.

## 11. AJAX requerido

| Endpoint | Método | Uso |
|----------|--------|-----|
| `/ajax/files/upload` | POST | multipart upload |
| `/ajax/files/{id}` | GET | metadata |
| `/ajax/files/{id}/delete` | POST | eliminar |
| `/files/{id}/download` | GET | stream (puede ser web no ajax) |

## 12. Respuestas esperadas

**Upload OK 201:**
```json
{
  "success": true,
  "message": "Archivo subido correctamente",
  "data": { "attachment": { "id": 55, "original_name": "circular.pdf", "size_bytes": 204800 } },
  "errors": {}
}
```

**Upload PHP rechazado 400:**
```json
{
  "success": false,
  "message": "Tipo de archivo no permitido",
  "data": null,
  "errors": { "file": ["La extensión php está prohibida"] }
}
```

## 13. Manejo de errores

| Error | Acción |
|-------|--------|
| move_uploaded_file fail | 500 + log; no fila BD |
| unlink fail en delete | log warning; decidir si borrar metadata |
| Path traversal attempt | 403/404 + security.log |
| Disco lleno | 500 mensaje amable |

Usar transacciones lógicas: si INSERT ok pero move fail → compensar.

## 14. Casos de prueba

| ID | Caso | Esperado |
|----|------|----------|
| FIL-01 | Upload PDF válido | 201 + archivo en storage |
| FIL-02 | Upload .php | Rechazo |
| FIL-03 | Upload .exe | Rechazo |
| FIL-04 | MIME falsificado (exe→pdf) | Rechazo si finfo no pdf |
| FIL-05 | Download sin auth | 401 |
| FIL-06 | Path `../` en parámetros | Bloqueado |
| FIL-07 | Acceso URL directa /uploads/... | Denegado |
| FIL-08 | Rename opaco | stored_name ≠ original |
| FIL-09 | Delete adjunto | File + row removed |
| FIL-10 | ZIP permitido | 201 |
| FIL-11 | DOCENTE download aviso propio feed | 200 |
| FIL-12 | Archivo > max MB | 400 |

## 15. Criterios de aceptación

- [ ] Whitelist y prohibidos implementados.
- [ ] Validación MIME con fileinfo.
- [ ] Rename + storage fuera de public.
- [ ] Download autenticado sin path traversal.
- [ ] Metadata sin exponer disk_path.
- [ ] `.htaccess` deny en uploads.
- [ ] Casos FIL P0 pasan.
- [ ] Auditoría upload/delete.

## 16. Dependencias

- SPEC 01 (rutas storage), 04, 05, 10, 11.
- Extensión PHP `fileinfo`.
- Permisos `files.*`.
- Config `UPLOAD_MAX_MB`, `UPLOAD_BASE_PATH`.

## 17. SKILL requerida

- `skills/file-upload/SKILL.md`
- `skills/security/SKILL.md`
- `skills/owasp/SKILL.md`
- `skills/ajax/SKILL.md`

## 18. Estado de implementación

**PENDING**
