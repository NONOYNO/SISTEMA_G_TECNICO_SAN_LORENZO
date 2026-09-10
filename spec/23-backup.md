# SPEC 23 — Backup (MySQL y storage)

**Proyecto:** Unidad Educativa Fiscomisional San Lorenzo  
**Stack:** PHP 8 + MySQL + PDO + MVC + Bootstrap 5 + AJAX + XAMPP  
**Módulo:** Respaldo de base de datos y archivos  
**Estado:** PENDING

---

## 1. Objetivo

Definir el proceso de backup y restauración de la base MySQL `ue_san_lorenzo` y de los directorios de almacenamiento (`storage/`, `uploads/`) para proteger la información institucional en entorno XAMPP.

## 2. Alcance

**Incluye:**
- Backup lógico MySQL (`mysqldump`).
- Backup de archivos (ZIP/copia) de storage/uploads.
- Convención de nombres y carpeta `storage/backups/` (fuera de public).
- Script CLI PHP/Bat/PowerShell.
- Opción ADMIN (dev) para disparar backup manual.
- Procedimiento de restore documentado.
- Retención (ej. últimos 7–14 backups).

**Excluye:**
- Replicación MySQL master-slave.
- Backup cloud obligatorio.
- Snapshots de todo el disco C:\.

## 3. Actores

| Actor | Capacidad |
|-------|-----------|
| Administrador técnico | Ejecuta CLI / tareas programadas |
| ADMIN app | Dispara backup manual si está habilitado |
| Otros roles | Sin acceso |

## 4. Requisitos

### Funcionales
- RF-B01: Generar dump SQL comprimido opcional (`.sql` o `.sql.zip`).
- RF-B02: Incluir rutinas/triggers si existen (`--routines --triggers --single-transaction` cuando InnoDB).
- RF-B03: Empaquetar `storage/` y `uploads/` excluyendo `storage/backups` recursivo infinito.
- RF-B04: Naming: `YYYYMMDD_HHMMSS_ue_san_lorenzo_db.sql` y `YYYYMMDD_HHMMSS_files.zip`.
- RF-B05: Manifest JSON con fecha, tamaño, hash SHA-256, usuario que disparó.
- RF-B06: Restore documentado: import SQL + unzip files.
- RF-B07: Retención: borrar backups más antiguos que N días (configurable).
- RF-B08: Registrar en `audit_logs` acción `BACKUP_CREATED` / `BACKUP_RESTORED` (restore solo CLI).

### No funcionales
- RNF-B01: Ejecución sin bloquear web requests largos (CLI preferido).
- RNF-B02: Espacio en disco verificado antes de backup.
- RNF-B03: Credenciales DB desde `.env`, no hardcode.

## 5. Flujo funcional

### Backup
```text
1. Validar entorno y espacio
2. mysqldump → storage/backups/db/...
3. Comprimir storage/uploads → storage/backups/files/...
4. Escribir manifest + hashes
5. Aplicar retención
6. Audit BACKUP_CREATED
```

### Restore (manual controlado)
```text
1. Poner app en mantenimiento
2. Restaurar SQL (mysql < dump)
3. Restaurar ZIP files
4. Verificar migrate status
5. Quit maintenance
6. Audit BACKUP_RESTORED
```

## 6. Reglas de negocio

- RN-B01: Backups **nunca** servidos como static files públicos.
- RN-B02: Restore requiere confirmación explícita y entorno autorizado.
- RN-B03: No incluir `.env` dentro del ZIP de files (riesgo de filtración); documentar backup secreto aparte.
- RN-B04: En XAMPP Windows usar rutas absolutas a `C:\xampp\mysql\bin\mysqldump.exe`.
- RN-B05: Fallo parcial → marcar backup como FAILED en manifest; no borrar el anterior bueno.
- RN-B06: Horario recomendado fuera de jornada (tarea Programador de Windows).

## 7. Estructura de datos

### Directorios
```text
storage/backups/
├── db/
├── files/
└── manifests/
```

### Manifest ejemplo
```json
{
  "created_at": "2026-09-08T15:30:00-05:00",
  "db_file": "20260908_153000_ue_san_lorenzo_db.sql",
  "files_zip": "20260908_153000_files.zip",
  "db_sha256": "...",
  "files_sha256": "...",
  "triggered_by": "cli|admin:1",
  "status": "success"
}
```

No requiere tabla obligatoria; opcional `backups` metadata en DB.

## 8. Validaciones

- Existencia de `mysqldump` y permisos de escritura.
- Nombre de DB allowlist (`ue_san_lorenzo`, `ue_san_lorenzo_test`).
- Parámetro retention `N` entero positivo.
- Web trigger: ADMIN + CSRF + `BACKUP_WEB=true` solo local.
- Verificar hash post-escritura.

## 9. Seguridad

- Directorio backups con `Deny from all` en Apache.
- Comando restore no expuesto por HTTP en producción.
- No pasar credenciales en querystring.
- Evitar `shell_exec` con input usuario; args fijos escapados.
- Cifrado opcional del ZIP con contraseña de entorno (documentado).
- Cumple A05/A08/A10 (sin SSRF ni paths libres).

## 10. Interfaz

- CLI mensajes claros.
- ADMIN (opcional): botón “Generar backup” + lista de manifests (sin download público directo; descarga autenticada stream).
- Banner de mantenimiento durante restore.

## 11. AJAX requerido

| Endpoint | Método | Uso |
|----------|--------|-----|
| `/ajax/admin/backups` | GET | Listar manifests |
| `/ajax/admin/backups/run` | POST | Disparar backup |
| `/ajax/admin/backups/download/{id}` | GET | Descarga autenticada (opcional) |

## 12. Respuestas esperadas

```json
{
  "success": true,
  "message": "Backup generado",
  "data": {
    "manifest": "20260908_153000.json",
    "db_size": 1048576,
    "files_size": 5242880
  }
}
```

**Fallo espacio:**
```json
{
  "success": false,
  "message": "Espacio en disco insuficiente",
  "code": "DISK_FULL"
}
```

## 13. Manejo de errores

- mysqldump exit ≠ 0 → FAILED + log `database.log`.
- ZIP error → FAILED; conservar dump DB si está íntegro.
- Timeout PHP: preferir CLI; aumentar `max_execution_time` solo en script backup.
- Restore fallido → no borrar backup origen; procedimiento de recuperación documentado.

## 14. Casos de prueba

| ID | Caso | Esperado |
|----|------|----------|
| BKP-001 | Backup CLI DB | Archivo SQL creado |
| BKP-002 | Backup files | ZIP con uploads |
| BKP-003 | Manifest hash | Coincide |
| BKP-004 | Retención | Antiguos eliminados |
| BKP-005 | DOCENTE run backup | 403 |
| BKP-006 | Download sin auth | 401 |
| BKP-007 | Restore en test DB | Datos recuperados |
| BKP-008 | Path traversal id download | 400 |
| BKP-009 | Disk full simulado | Error controlado |
| BKP-010 | Audit BACKUP_CREATED | Registrado |

## 15. Criterios de aceptación

- [ ] Backup MySQL reproducible desde XAMPP.
- [ ] Backup storage/uploads separado y completo.
- [ ] Manifest + hash.
- [ ] Retención configurada.
- [ ] Restore documentado y probado en DB test.
- [ ] Sin exposición pública de backups.

## 16. Dependencias

- `spec/24-despliegue-xampp.md`, `spec/22-auditoria.md`, `spec/21-owasp-top10.md`, `spec/04-base-datos.md`
- Skills: `deployment`, `mysql`, `security`

## 17. SKILL requerida

```text
skills/backup/SKILL.md
```

(Complementaria: `skills/deployment/SKILL.md`)

## 18. Estado de implementación

**PENDING**
