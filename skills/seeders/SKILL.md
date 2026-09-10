---
name: seeders
description: Crea seeders de datos de prueba para PortalInfor (roles, permisos, usuarios demo, avisos). Usar para poblar XAMPP local y generar credenciales.md de desarrollo.
---

# Seeders — PortalInfor

## Nombre

`seeders`

## Propósito

Poblar la base de datos con datos iniciales y de prueba reproducibles: roles, permisos, usuarios por perfil, y opcionalmente avisos/notificaciones de demostración — siempre orientados a desarrollo/QA, nunca a secretos reales de producción.

## Cuándo usarla

- Tras migraciones en entorno limpio.
- Al preparar demos para Rectorado/Vicerrectorado/Docentes.
- Al escribir `credenciales.md` de prueba.
- Antes de baterías de testing funcional.
- Al resetear datos locales de forma controlada.

## SPEC relacionada

- `spec/19-seeders.md`
- `spec/09-roles-permisos.md`
- `spec/08-usuarios.md`
- `spec/18-migraciones.md`
- `spec/20-pruebas.md`

## Pasos de implementación

1. Leer `spec/19-seeders.md`.
2. Crear seeders en `database/seeders/` (orden explícito):
   1. Roles
   2. Permissions
   3. RolePermissionSeeder
   4. UserSeeder (admin, rector, vicerrector, docente)
   5. Demo data (avisos/notificaciones) opcional
3. Passwords con `password_hash`; documentar planos solo en `credenciales.md` de desarrollo.
4. Hacer seeders idempotentes cuando sea posible (`firstOrCreate` / upsert por slug/email).
5. Ejecutar runner: `php database/seed.php` (o el comando del proyecto).
6. Actualizar `credenciales.md` con usuarios demo.
7. Marcar claramente: **solo desarrollo/pruebas**.
8. No ejecutar seeders destructivos en producción.

### Credenciales demo sugeridas (desarrollo)

```text
admin / Admin123!
rector / Rector123!
vicerrector / Vicerrector123!
docente / Docente123!
```

Correos: `*@sanlorenzo.edu.ec` (o dominio definido en SPEC).

## Convenciones de código

- Un seeder = una responsabilidad.
- Usar slugs estables para roles/permisos.
- Datos en español institucional (nombres, títulos de avisos demo).
- No hardcodear IDs numéricos frágiles si se puede usar slug/email.
- Separar `DatabaseSeeder` orquestador.
- Factories opcionales para volumen de pruebas.
- Nunca imprimir hashes a logs públicos.

## Checklist de seguridad

- [ ] Contraseñas de seeder solo para local/QA.
- [ ] `credenciales.md` no contiene secretos productivos.
- [ ] Seed runner no expuesto por HTTP.
- [ ] Producción: seeders deshabilitados o require flag explícito.
- [ ] Usuarios demo con mínimo privilegio realista (excepto admin).
- [ ] No reutilizar estas claves en internet.

## Checklist de pruebas

- [ ] Migrar + seed en BD vacía OK.
- [ ] Re-seed idempotente no duplica roles/usuarios clave.
- [ ] Login funciona con las 4 cuentas demo.
- [ ] Matriz de permisos del seeder coincide con SPEC.
- [ ] `credenciales.md` actualizado y coherente.
- [ ] Datos demo de avisos/notificaciones visibles según rol.

## Errores comunes a evitar

- Seedear producción “para probar rápido”.
- Passwords en texto plano en tabla `users`.
- IDs fijos que chocan al re-seed parcial.
- Olvidar role_permissions → usuarios sin acceso real.
- Credenciales distintas entre seeder y `credenciales.md`.
- Datos basura sin valor para QA.

## Criterio de done

Seeders está **done** cuando migrar+seed reproduce un entorno usable, las 4 cuentas institucionales autentican, permisos coinciden con SPEC, `credenciales.md` está alineado, y está documentado que son solo para desarrollo/pruebas.
