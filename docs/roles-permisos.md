# Roles y permisos

RBAC con tablas `roles`, `permissions`, `user_roles`, `role_permissions`.

Roles de sistema: ADMIN, RECTOR, VICERRECTOR, DOCENTE (no eliminables).

La UI de edición de roles permite sincronizar permisos por grupo. La autorización se valida siempre en backend (middleware `permission:*`).
