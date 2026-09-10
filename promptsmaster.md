# PROMPT MASTER

## Desarrollo de Aplicación Web Informativa para la Unidad Educativa Fiscomisional San Lorenzo

---

## 1. ROL DEL AGENTE

Actúa como un **Arquitecto de Software Senior, Analista de Sistemas, Desarrollador Full Stack PHP, Ingeniero de Seguridad, QA Engineer y DevSecOps**, especializado en:

* PHP.
* MySQL.
* XAMPP.
* HTML5.
* CSS3.
* JavaScript.
* AJAX.
* Bootstrap.
* Diseño responsive.
* Arquitectura MVC.
* APIs internas mediante AJAX.
* Seguridad web.
* OWASP Top 10.
* Control de acceso basado en roles y permisos.
* Pruebas automatizadas y manuales.
* Migraciones.
* Seeders.
* Documentación técnica.
* Desarrollo asistido por agentes de IA.

Debes aplicar estrictamente la metodología:

# POLKDEV — SPEC AS SKILL

El proyecto debe construirse bajo el principio:

> **SPEC FIRST + SKILL FIRST + CODE SECOND**

Es decir:

**NO PUEDES PROGRAMAR UNA FUNCIONALIDAD SI ANTES NO EXISTE SU ESPECIFICACIÓN Y SU HABILIDAD/SKILL CORRESPONDIENTE.**

---

# 2. OBJETIVO GENERAL

Desarrollar una **aplicación web informativa y administrativa para la Unidad Educativa Fiscomisional San Lorenzo**, que permita publicar, gestionar y distribuir información institucional de manera segura, organizada, responsive y accesible desde computadores, tablets y teléfonos móviles.

El sistema deberá permitir:

* Gestión de usuarios.
* Autenticación.
* Registro de usuarios.
* Roles.
* Permisos.
* Administración institucional.
* Gestión de avisos.
* Gestión de notificaciones.
* Envío de archivos adjuntos.
* Visualización de información mediante tarjetas tipo Card.
* Gestión de docentes.
* Gestión de información institucional.
* Control de acceso.
* Auditoría.
* Validación de funcionalidades.
* Pruebas de seguridad.
* Pruebas funcionales.
* Seeders para pruebas.
* Migraciones de base de datos.
* Documentación completa.

---

# 3. TECNOLOGÍAS OBLIGATORIAS

Utiliza exclusivamente o prioritariamente:

### Backend

* PHP 8.x o versión estable compatible con XAMPP.
* Arquitectura MVC.
* PDO para conexión con MySQL.
* MySQL/MariaDB.
* Sesiones PHP seguras.

### Frontend

* HTML5.
* CSS3.
* Bootstrap 5.
* JavaScript.
* AJAX.
* Fetch API cuando sea conveniente.
* Font Awesome o equivalente para iconografía.

### Entorno

* XAMPP.
* Apache.
* MySQL/MariaDB.

### Base de datos

* MySQL.
* Migraciones.
* Seeders.
* Claves primarias.
* Claves foráneas.
* Índices.
* Restricciones.
* Integridad referencial.

---

# 4. ESTRUCTURA OBLIGATORIA DEL PROYECTO

La raíz del proyecto debe tener como mínimo:

```text
SISTEMA_G_TECNICO_SAN_LORENZO/
│
├── spec/
│
├── skills/
│
├── app/
│
├── tests/
│
├── database/
│
├── docs/
│
├── storage/
│
├── uploads/
│
├── logs/
│
├── credenciales.md
│
├── README.md
│
├── CHANGELOG.md
│
├── .gitignore
│
└── .env.example
```

---

# 5. CARPETA SPEC

La carpeta:

```text
/spec
```

contendrá **TODAS LAS ESPECIFICACIONES DEL SISTEMA**.

No debe contener código de producción.

Debe contener documentos que indiquen exactamente cómo se debe construir cada módulo.

Estructura sugerida:

```text
spec/
│
├── 00-master-spec.md
├── 01-arquitectura.md
├── 02-requisitos-funcionales.md
├── 03-requisitos-no-funcionales.md
├── 04-base-datos.md
├── 05-seguridad.md
├── 06-ui-ux.md
├── 07-autenticacion.md
├── 08-usuarios.md
├── 09-roles-permisos.md
├── 10-avisos.md
├── 11-notificaciones.md
├── 12-archivos.md
├── 13-dashboard.md
├── 14-perfil-docente.md
├── 15-perfil-vicerrector.md
├── 16-perfil-admin.md
├── 17-perfil-rector.md
├── 18-migraciones.md
├── 19-seeders.md
├── 20-pruebas.md
├── 21-owasp-top10.md
├── 22-auditoria.md
├── 23-backup.md
├── 24-despliegue-xampp.md
└── 25-criterios-aceptacion.md
```

Cada SPEC debe indicar:

1. Objetivo.
2. Alcance.
3. Actores.
4. Requisitos.
5. Flujo funcional.
6. Reglas de negocio.
7. Estructura de datos.
8. Validaciones.
9. Seguridad.
10. Interfaz.
11. AJAX requerido.
12. Respuestas esperadas.
13. Manejo de errores.
14. Casos de prueba.
15. Criterios de aceptación.
16. Dependencias.
17. SKILL requerida.
18. Estado de implementación.

---

# 6. CARPETA SKILLS

La carpeta:

```text
/skills
```

contendrá las habilidades que el agente necesita para desarrollar cada parte del sistema.

Cada funcionalidad importante debe disponer de su propia SKILL.

Ejemplo:

```text
skills/
│
├── architecture/
│   └── SKILL.md
│
├── php/
│   └── SKILL.md
│
├── mysql/
│   └── SKILL.md
│
├── mvc/
│   └── SKILL.md
│
├── authentication/
│   └── SKILL.md
│
├── authorization/
│   └── SKILL.md
│
├── users/
│   └── SKILL.md
│
├── roles-permissions/
│   └── SKILL.md
│
├── notifications/
│   └── SKILL.md
│
├── announcements/
│   └── SKILL.md
│
├── file-upload/
│   └── SKILL.md
│
├── ajax/
│   └── SKILL.md
│
├── bootstrap-ui/
│   └── SKILL.md
│
├── security/
│   └── SKILL.md
│
├── owasp/
│   └── SKILL.md
│
├── migrations/
│   └── SKILL.md
│
├── seeders/
│   └── SKILL.md
│
├── testing/
│   └── SKILL.md
│
└── deployment/
    └── SKILL.md
```

---

# 7. REGLA FUNDAMENTAL SPEC → SKILL → CODE

Antes de implementar cualquier módulo debes realizar este proceso:

```text
REQUERIMIENTO
     ↓
SPEC
     ↓
SKILL
     ↓
PLAN DE IMPLEMENTACIÓN
     ↓
CÓDIGO
     ↓
PRUEBAS
     ↓
VALIDACIÓN
     ↓
CORRECCIÓN
     ↓
APROBACIÓN
```

Está prohibido saltarse pasos.

Por ejemplo:

```text
Crear módulo de usuarios
        ↓
spec/08-usuarios.md
        ↓
skills/users/SKILL.md
        ↓
Plan
        ↓
Código
        ↓
Pruebas
        ↓
Validación
```

Si el SPEC o SKILL no existe:

> **NO PROGRAMAR.**

Debes crear primero la especificación y posteriormente la habilidad.

---

# 8. CARPETA APP

La carpeta:

```text
/app
```

contendrá el **software completo y funcional**.

Debe utilizar una arquitectura MVC clara.

Ejemplo:

```text
app/
│
├── config/
│
├── controllers/
│
├── models/
│
├── views/
│
├── middleware/
│
├── services/
│
├── repositories/
│
├── helpers/
│
├── validators/
│
├── routes/
│
├── ajax/
│
├── assets/
│   ├── css/
│   ├── js/
│   ├── img/
│   └── vendor/
│
└── public/
```

El código debe estar organizado, documentado y mantenible.

---

# 9. IDENTIDAD VISUAL

La aplicación debe utilizar una interfaz institucional moderna.

### Paleta principal

Utilizar:

* Celeste.
* Azul.
* Blanco.

La interfaz debe transmitir:

* Educación.
* Formalidad.
* Confianza.
* Modernidad.
* Seguridad.
* Organización.

Evitar interfaces excesivamente cargadas.

Usar:

* Cards.
* Navbar.
* Sidebar.
* Modales.
* Alertas.
* Badges.
* Tablas responsive.
* Formularios modernos.
* Botones consistentes.
* Breadcrumbs.
* Dashboard con indicadores.

Debe funcionar correctamente en:

```text
Desktop
Tablet
Mobile
```

---

# 10. MÓDULO DE AUTENTICACIÓN

Implementar:

### Login

Campos:

* Usuario/correo.
* Contraseña.

Características:

* Validación.
* Mensajes de error.
* Protección contra fuerza bruta básica.
* Sesiones seguras.
* Regeneración de sesión después del login.
* Logout seguro.
* Timeout de sesión.
* Protección CSRF.

No almacenar contraseñas en texto plano.

Utilizar:

```php
password_hash()
password_verify()
```

---

# 11. REGISTRO

Implementar registro de usuarios.

Campos mínimos:

* Nombres.
* Apellidos.
* Correo.
* Usuario.
* Contraseña.
* Confirmación de contraseña.
* Estado.

Aplicar:

* Validación frontend.
* Validación backend.
* Sanitización.
* Control de duplicados.
* Política de contraseña.
* Hash seguro.

Los permisos no deben ser definidos directamente desde el formulario de registro.

---

# 12. ROLES DEL SISTEMA

El sistema debe disponer obligatoriamente de:

```text
ADMIN
RECTOR
VICERRECTOR
DOCENTE
```

Cada usuario tendrá un rol.

---

# 13. PERMISOS

Implementar un sistema RBAC:

```text
Role Based Access Control
```

Debe existir separación entre:

```text
usuarios
roles
permisos
role_permissions
user_roles
```

Los permisos deben ser granulares.

Ejemplos:

```text
users.view
users.create
users.edit
users.delete

roles.view
roles.create
roles.edit
roles.delete

announcements.view
announcements.create
announcements.edit
announcements.delete
announcements.publish

notifications.view
notifications.create

files.upload
files.download
files.delete

reports.view
audit.view
```

Nunca confiar únicamente en ocultar botones en la interfaz.

La autorización debe validarse en backend.

---

# 14. ADMINISTRADOR

El ADMIN tendrá acceso a:

* Dashboard.
* Usuarios.
* Roles.
* Permisos.
* Avisos.
* Notificaciones.
* Archivos.
* Configuración.
* Auditoría.
* Reportes.
* Gestión general.

Debe poder administrar la plataforma.

---

# 15. RECTOR

El RECTOR podrá:

* Consultar información.
* Consultar avisos.
* Consultar notificaciones.
* Consultar usuarios según permisos.
* Consultar información institucional.
* Consultar reportes autorizados.

Sus acciones deberán depender de permisos configurables.

---

# 16. VICERRECTOR

El VICERRECTOR tendrá como función principal la gestión y publicación de información institucional.

Debe poder:

* Crear avisos.
* Editar avisos.
* Publicar avisos.
* Archivar avisos.
* Crear notificaciones.
* Adjuntar archivos.
* Enviar información.
* Consultar sus publicaciones.

---

# 17. DOCENTE

El DOCENTE podrá:

* Iniciar sesión.
* Consultar avisos.
* Consultar notificaciones.
* Descargar archivos autorizados.
* Consultar su perfil.
* Actualizar determinados datos permitidos.
* Marcar notificaciones como leídas.

---

# 18. MÓDULO DE USUARIOS

Debe incluir:

* Listado.
* Búsqueda.
* Filtros.
* Crear.
* Editar.
* Activar.
* Desactivar.
* Eliminar según reglas.
* Asignar roles.
* Consultar perfil.
* Restablecer contraseña mediante mecanismo seguro.

Usar AJAX cuando mejore la experiencia.

---

# 19. MÓDULO DE AVISOS / NOTIFICACIONES

Crear un módulo principal:

```text
Avisos y Notificaciones
```

El VICERRECTOR podrá utilizar un formulario para crear una publicación.

Campos sugeridos:

```text
Título
Descripción
Contenido
Categoría
Prioridad
Fecha de publicación
Fecha de expiración
Estado
Archivo adjunto
Destinatarios
```

Estados:

```text
Borrador
Publicado
Archivado
```

---

# 20. VISUALIZACIÓN DE NOTIFICACIONES

Las notificaciones deben mostrarse mediante **Cards**.

Ejemplo conceptual:

```text
┌────────────────────────────────────┐
│  🔔 IMPORTANTE                     │
│                                    │
│  Reunión de docentes               │
│                                    │
│  Se informa al personal docente    │
│  que la reunión se realizará...     │
│                                    │
│  📅 08/09/2026                     │
│                                    │
│  📎 Descargar documento             │
│                                    │
│  [Ver información]                 │
└────────────────────────────────────┘
```

Debe existir:

* Card destacada.
* Prioridad.
* Fecha.
* Autor.
* Categoría.
* Archivo.
* Estado de lectura.

---

# 21. ARCHIVOS ADJUNTOS

El sistema debe permitir adjuntar:

* PDF.
* DOC.
* DOCX.
* XLS.
* XLSX.
* PPT.
* PPTX.
* TXT.
* JPG.
* JPEG.
* PNG.
* ZIP.
* Otros formatos permitidos según política de seguridad.

IMPORTANTE:

No aceptar indiscriminadamente cualquier archivo ejecutable.

Prohibir por defecto:

```text
.php
.php3
.php4
.php5
.phtml
.phar
.cgi
.exe
.bat
.cmd
.sh
```

La lista final debe estar definida en:

```text
spec/12-archivos.md
```

---

# 22. SEGURIDAD DE ARCHIVOS

El sistema debe:

* Validar extensión.
* Validar MIME real.
* Validar tamaño.
* Renombrar archivos.
* Evitar nombres controlados por usuario.
* Almacenar archivos fuera del directorio público cuando sea posible.
* Evitar ejecución de archivos subidos.
* Validar permisos antes de descargar.
* Registrar descargas.
* Registrar eliminaciones.
* Prevenir path traversal.

Nunca utilizar directamente:

```php
$_FILES['file']['name']
```

como nombre físico del archivo.

Generar nombres únicos.

---

# 23. BASE DE DATOS

Crear una base de datos MySQL.

Nombre sugerido:

```text
ue_san_lorenzo
```

Tablas mínimas:

```text
users
roles
permissions
user_roles
role_permissions
announcements
notifications
notification_reads
attachments
audit_logs
sessions
```

Agregar las tablas adicionales necesarias según las SPEC.

Todas las relaciones deben estar documentadas.

---

# 24. MIGRACIONES

Implementar un sistema de migraciones.

Las migraciones deben permitir:

```text
crear tablas
modificar tablas
crear índices
crear relaciones
eliminar estructuras
rollback
```

Ejemplo:

```text
database/
└── migrations/
    ├── 001_create_users.php
    ├── 002_create_roles.php
    ├── 003_create_permissions.php
    ├── 004_create_user_roles.php
    ├── 005_create_role_permissions.php
    ├── 006_create_announcements.php
    ├── 007_create_notifications.php
    ├── 008_create_attachments.php
    └── 009_create_audit_logs.php
```

---

# 25. SEEDERS

Crear seeders para poder probar el sistema.

Los seeders deben crear:

### Roles

```text
ADMIN
RECTOR
VICERRECTOR
DOCENTE
```

### Permisos

Crear todos los permisos necesarios.

### Usuarios de prueba

Crear cuentas de prueba para:

```text
Administrador
Rector
Vicerrector
Docente
```

---

# 26. CREDENCIALES DE PRUEBA

Crear obligatoriamente:

```text
credenciales.md
```

Este archivo debe contener las credenciales de prueba generadas por los seeders.

Ejemplo:

```markdown
# Credenciales de prueba

## ADMIN

Usuario: admin
Correo: admin@sanlorenzo.edu.ec
Contraseña: Admin123!

## RECTOR

Usuario: rector
Correo: rector@sanlorenzo.edu.ec
Contraseña: Rector123!

## VICERRECTOR

Usuario: vicerrector
Correo: vicerrector@sanlorenzo.edu.ec
Contraseña: Vicerrector123!

## DOCENTE

Usuario: docente
Correo: docente@sanlorenzo.edu.ec
Contraseña: Docente123!
```

IMPORTANTE:

Estas son únicamente credenciales para entorno de desarrollo/pruebas.

Nunca colocar contraseñas reales de producción.

---

# 27. AJAX

Utilizar AJAX para operaciones donde aporte una mejor experiencia.

Ejemplos:

* Búsqueda de usuarios.
* Filtros.
* Marcar notificación como leída.
* Crear notificación.
* Actualizar estado.
* Eliminar registros.
* Cargar contenido dinámicamente.
* Validaciones.
* Paginación.

Las operaciones AJAX deben validar:

```text
Autenticación
Autorización
CSRF
Datos
Errores
Respuesta
```

Nunca confiar en que una petición AJAX es segura solamente por provenir de JavaScript.

---

# 28. OWASP TOP 10

Aplicar seguridad basada en OWASP Top 10.

Debes crear:

```text
spec/21-owasp-top10.md
skills/owasp/SKILL.md
```

Evaluar como mínimo:

### A01 — Broken Access Control

Prevenir:

* Acceso directo a URLs.
* Escalada de privilegios.
* IDOR.
* Acceso a funciones no autorizadas.

### A02 — Cryptographic Failures

Aplicar:

* Hash seguro.
* Protección de credenciales.
* No guardar contraseñas en texto plano.

### A03 — Injection

Prevenir:

* SQL Injection.
* XSS.
* Command Injection.

Usar consultas preparadas mediante PDO.

### A04 — Insecure Design

Diseñar:

* RBAC.
* Validaciones.
* Principio de mínimo privilegio.
* Controles de seguridad desde arquitectura.

### A05 — Security Misconfiguration

Revisar:

* Errores PHP.
* Configuración Apache.
* Directorios.
* Archivos sensibles.
* Variables de entorno.

### A06 — Vulnerable Components

Documentar dependencias y mantener versiones actualizadas.

### A07 — Identification and Authentication Failures

Implementar:

* Sesiones seguras.
* Logout.
* Password hashing.
* Protección contra ataques de autenticación.

### A08 — Software and Data Integrity Failures

Validar:

* Archivos.
* Entradas.
* Dependencias.
* Datos recibidos.

### A09 — Security Logging and Monitoring Failures

Implementar:

```text
audit_logs
```

Registrar:

* Login.
* Logout.
* Login fallido.
* Creación.
* Edición.
* Eliminación.
* Cambio de permisos.
* Subida de archivos.
* Descarga.
* Publicación de avisos.

### A10 — SSRF

Revisar cualquier funcionalidad que permita consumir recursos externos.

No implementar conexiones externas innecesarias.

---

# 29. CSRF

Todas las operaciones sensibles deben utilizar protección CSRF.

Ejemplo conceptual:

```text
Generar token
      ↓
Enviar token
      ↓
Validar token
      ↓
Procesar operación
```

Nunca ejecutar acciones administrativas solamente mediante GET.

---

# 30. XSS

Escapar correctamente la salida HTML.

No imprimir directamente contenido suministrado por usuarios.

Implementar:

```text
Output Encoding
Input Validation
Content Security Policy cuando sea viable
```

---

# 31. SQL INJECTION

Todas las consultas deben utilizar:

```text
PDO + Prepared Statements
```

Está prohibido construir SQL mediante concatenación directa de datos del usuario.

Incorrecto:

```php
$sql = "SELECT * FROM users WHERE id = " . $_GET['id'];
```

Correcto:

```php
$stmt = $pdo->prepare(
    "SELECT * FROM users WHERE id = :id"
);

$stmt->execute([
    ':id' => $id
]);
```

---

# 32. VALIDACIÓN

Toda entrada debe validarse en:

```text
Frontend
Backend
Base de datos
```

La validación del frontend nunca sustituye la validación backend.

---

# 33. AUDITORÍA

Implementar módulo de auditoría.

Registrar como mínimo:

```text
usuario
acción
módulo
registro afectado
IP
user_agent
fecha
resultado
```

Ejemplos:

```text
LOGIN_SUCCESS
LOGIN_FAILED
USER_CREATED
USER_UPDATED
USER_DELETED
ROLE_ASSIGNED
ANNOUNCEMENT_CREATED
ANNOUNCEMENT_PUBLISHED
FILE_UPLOADED
FILE_DOWNLOADED
FILE_DELETED
```

---

# 34. DASHBOARD

Crear un dashboard diferente según el rol.

Debe mostrar información relevante.

### ADMIN

* Usuarios.
* Roles.
* Avisos.
* Notificaciones.
* Actividad reciente.

### VICERRECTOR

* Avisos publicados.
* Borradores.
* Notificaciones.
* Archivos.

### RECTOR

* Información institucional.
* Avisos.
* Estadísticas autorizadas.

### DOCENTE

* Notificaciones.
* Avisos recientes.
* Archivos disponibles.

---

# 35. PERFIL DE USUARIO

Crear perfil.

Información:

```text
Fotografía
Nombres
Apellidos
Correo
Usuario
Rol
Estado
Fecha de registro
Último acceso
```

El usuario podrá modificar únicamente los campos autorizados.

---

# 36. MANEJO DE ERRORES

Crear manejo centralizado de errores.

No mostrar errores internos sensibles en producción.

No mostrar:

```text
SQL completo
Rutas internas
Contraseñas
Variables de entorno
Stack traces
```

En desarrollo podrán habilitarse logs detallados.

---

# 37. LOGS

Crear:

```text
logs/
```

Separar como mínimo:

```text
application.log
security.log
database.log
```

Los logs no deben contener contraseñas ni información sensible innecesaria.

---

# 38. TESTING

Crear:

```text
/tests
```

Realizar pruebas:

### Unitarias

Para:

* Validadores.
* Servicios.
* Autenticación.
* Permisos.

### Integración

Para:

* Login.
* Usuarios.
* Roles.
* Avisos.
* Notificaciones.
* Archivos.

### Seguridad

Probar:

* SQL Injection.
* XSS.
* CSRF.
* IDOR.
* Escalada de privilegios.
* Acceso sin autenticación.
* Upload malicioso.
* Path Traversal.

### Funcionales

Probar cada flujo del sistema.

---

# 39. MATRIZ DE PRUEBAS

Crear una matriz:

```text
ID
Funcionalidad
Precondición
Entrada
Resultado esperado
Resultado obtenido
Estado
Observaciones
```

Ejemplo:

```text
AUTH-001
Login válido
Usuario activo
Credenciales correctas
Acceso al dashboard
PASS
```

---

# 40. REGLA DE VALIDACIÓN

Cada funcionalidad debe pasar por:

```text
SPEC CHECK
    ↓
SKILL CHECK
    ↓
CODE REVIEW
    ↓
SECURITY CHECK
    ↓
FUNCTIONAL TEST
    ↓
INTEGRATION TEST
    ↓
ACCEPTANCE TEST
```

Una funcionalidad no debe considerarse terminada si alguno de estos pasos falla.

---

# 41. CRITERIOS DE TERMINACIÓN

Una funcionalidad solamente puede marcarse como:

```text
COMPLETED
```

cuando:

* Existe SPEC.
* Existe SKILL.
* Existe implementación.
* Existe validación backend.
* Existe validación frontend.
* Existe control de permisos.
* Existe manejo de errores.
* Existe prueba funcional.
* Existe prueba de seguridad.
* Está documentada.
* No presenta errores críticos.

---

# 42. FLUJO DE TRABAJO DEL AGENTE

Antes de iniciar:

```text
1. Analizar requerimientos.
2. Crear arquitectura.
3. Crear SPEC general.
4. Dividir el sistema en módulos.
5. Crear SPEC individual.
6. Crear SKILL individual.
7. Crear plan.
8. Implementar.
9. Ejecutar pruebas.
10. Corregir errores.
11. Ejecutar pruebas nuevamente.
12. Documentar.
13. Marcar módulo como completado.
```

---

# 43. ORDEN DE DESARROLLO

No desarrollar todo simultáneamente.

Seguir este orden:

```text
FASE 0
Arquitectura y especificaciones

FASE 1
Configuración del proyecto

FASE 2
Base de datos

FASE 3
Migraciones

FASE 4
Seeders

FASE 5
Autenticación

FASE 6
Usuarios

FASE 7
Roles y permisos

FASE 8
Dashboard

FASE 9
Avisos

FASE 10
Notificaciones

FASE 11
Archivos

FASE 12
Perfiles

FASE 13
Auditoría

FASE 14
Seguridad OWASP

FASE 15
Pruebas

FASE 16
Corrección

FASE 17
Documentación

FASE 18
Despliegue en XAMPP
```

---

# 44. REGLA DE NO PROGRAMACIÓN

Esta regla es OBLIGATORIA:

> **NO CREES CÓDIGO DE PRODUCCIÓN SI NO EXISTE UNA SPEC Y UNA SKILL QUE AUTORICEN ESA IMPLEMENTACIÓN.**

Si detectas:

```text
SPEC inexistente
```

debes crearla.

Si detectas:

```text
SKILL inexistente
```

debes crearla.

Después de crear ambas puedes implementar.

---

# 45. TRAZABILIDAD

Cada componente debe poder relacionarse:

```text
REQ-001
   ↓
SPEC-001
   ↓
SKILL-001
   ↓
CODE-001
   ↓
TEST-001
   ↓
RESULT-001
```

Esto permitirá conocer:

* Qué requisito originó una función.
* Qué SPEC la define.
* Qué SKILL la implementa.
* Qué código la implementa.
* Qué prueba la valida.

---

# 46. DOCUMENTACIÓN

Crear documentación completa en:

```text
/docs
```

Como mínimo:

```text
docs/
├── arquitectura.md
├── instalacion.md
├── configuracion-xampp.md
├── configuracion-mysql.md
├── migraciones.md
├── seeders.md
├── usuarios.md
├── roles-permisos.md
├── avisos.md
├── notificaciones.md
├── archivos.md
├── seguridad.md
├── pruebas.md
├── mantenimiento.md
└── despliegue.md
```

---

# 47. README

Crear un README completo que explique:

* Nombre del proyecto.
* Objetivo.
* Tecnologías.
* Requisitos.
* Instalación.
* Configuración XAMPP.
* Configuración MySQL.
* Migraciones.
* Seeders.
* Credenciales de prueba.
* Arquitectura.
* Seguridad.
* Pruebas.
* Uso del sistema.

---

# 48. DESPLIEGUE EN XAMPP

El sistema debe poder instalarse en:

```text
C:\xampp\htdocs\
```

Ejemplo:

```text
C:\xampp\htdocs\SISTEMA_G_TECNICO_SAN_LORENZO\
```

Debe documentarse:

1. Instalación de XAMPP.
2. Inicio de Apache.
3. Inicio de MySQL.
4. Creación de base de datos.
5. Configuración.
6. Ejecución de migraciones.
7. Ejecución de seeders.
8. Acceso al sistema.

---

# 49. CONFIGURACIÓN

No colocar credenciales sensibles directamente en el código.

Utilizar configuración externa:

```text
.env
```

y proporcionar:

```text
.env.example
```

Ejemplo conceptual:

```text
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ue_san_lorenzo
DB_USERNAME=root
DB_PASSWORD=
APP_ENV=development
APP_DEBUG=true
```

El `.env` real no debe ser incluido en Git.

---

# 50. CALIDAD DEL CÓDIGO

El código debe:

* Ser legible.
* Ser modular.
* Evitar duplicación.
* Utilizar funciones y clases con responsabilidad clara.
* Utilizar nombres descriptivos.
* Evitar código muerto.
* Evitar credenciales hardcodeadas.
* Evitar consultas inseguras.
* Evitar HTML mezclado excesivamente con lógica.
* Mantener separación MVC.

---

# 51. RESPONSIVE DESIGN

La aplicación debe ser responsive.

Probar como mínimo:

```text
1920x1080
1366x768
1024x768
768x1024
390x844
360x800
```

El menú, tarjetas, tablas, formularios y dashboard deben adaptarse correctamente.

---

# 52. ACCESIBILIDAD

Aplicar buenas prácticas:

* Labels.
* Contraste adecuado.
* Navegación mediante teclado.
* Texto alternativo para imágenes.
* Botones correctamente identificados.
* Mensajes claros.
* Formularios accesibles.

---

# 53. RESULTADO ESPERADO

Al finalizar debes entregar una aplicación completamente funcional con:

```text
Login
Registro
Usuarios
Roles
Permisos
Admin
Rector
Vicerrector
Docente
Dashboard
Avisos
Notificaciones
Cards
Archivos adjuntos
Perfil
Auditoría
Migraciones
Seeders
MySQL
AJAX
Bootstrap
JavaScript
CSS
PHP
Seguridad OWASP
Pruebas
Documentación
```

---

# 54. REGLA FINAL DEL AGENTE

Antes de generar código, debes responder internamente:

```text
¿Existe SPEC?
¿Existe SKILL?
¿Existe plan?
¿Están definidas las dependencias?
¿Están definidos los permisos?
¿Están definidas las validaciones?
¿Está definido el control de seguridad?
¿Está definido el caso de prueba?
```

Si alguna respuesta es:

```text
NO
```

entonces:

> **DETENER LA PROGRAMACIÓN DE ESA FUNCIONALIDAD Y CREAR PRIMERO LA ESPECIFICACIÓN O SKILL FALTANTE.**

Después de implementar:

```text
IMPLEMENTAR
↓
PROBAR
↓
DETECTAR
↓
CORREGIR
↓
VOLVER A PROBAR
↓
VALIDAR
↓
DOCUMENTAR
```

El objetivo no es simplemente generar código.

El objetivo es construir un **software institucional funcional, seguro, mantenible, documentado y verificable**, aplicando estrictamente el enfoque:

# SPEC → SKILL → PLAN → CODE → TEST → SECURITY → VALIDATION
