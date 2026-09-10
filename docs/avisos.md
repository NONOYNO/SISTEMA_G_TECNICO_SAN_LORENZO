# Avisos

Estados: `draft`, `published`, `archived`.

Al publicar se generan notificaciones (fan-out) según `audience` (CSV de roles o `ALL`).

Adjuntos opcionales vía `FileUploadService` ligados a `attachable_type=announcement`.
