# Despliegue en producción (migraciones sin pérdida de datos)

La landing, i18n y menú de usuario **no modifican** datos de clientes. El riesgo está en ejecutar migraciones pendientes sobre la base de datos existente.

## Principios

- Las migraciones Laravel del proyecto son en general **aditivas** (nuevas columnas/tablas).
- **Nunca** ejecutar `migrate:fresh`, `migrate:refresh` ni `db:wipe` en producción.
- Hacer **backup completo** de la base de datos (y copia de `.env` / `storage` si aplica) antes de cada deploy.

## Checklist de despliegue

```text
1. Backup completo de BD (y .env / storage si aplica)
2. (Opcional) Modo mantenimiento: php artisan down
3. git pull + composer install --no-dev --optimize-autoloader
4. Si aún existen columnas dish_of_day_* con datos en uso:
     php artisan webnu:migrate-dish-of-day-to-spotlight --dry-run
     php artisan webnu:migrate-dish-of-day-to-spotlight
5. php artisan migrate --force
6. php artisan config:cache && php artisan view:cache
7. php artisan up
8. Smoke test: login usuario real, editar 1 carta, QR, especial del día (daily_spotlight)
```

## Migraciones recientes relevantes

| Migración | Impacto |
|-----------|---------|
| `2026_05_21_120100_add_daily_highlights_to_companies_table` | Añade `dish_of_day_product_id` y `chef_suggestion_product_id`. |
| `2026_05_21_130000_replace_daily_highlights_with_spotlight_on_companies` | En **MySQL**, si existían columnas antiguas, las **elimina** y crea `daily_spotlight` / `daily_spotlight_price` **sin copiar** datos automáticamente. |
| `2026_05_21_140000_ensure_daily_spotlight_columns_on_companies` | Idempotente: asegura columnas spotlight en SQLite/MySQL. |
| `2026_05_20_120000_add_menu_translations` | Nueva tabla de traducciones; no altera platos existentes. |
| Plan / onboarding en `users` | Columnas nuevas con valores por defecto. |

### Especial del día (`dish_of_day` → `daily_spotlight`)

Si en producción aún hay empresas con `dish_of_day_product_id` rellenado y `daily_spotlight` vacío:

1. Ejecutar **antes** de `migrate` (o antes de que la migración `130000` elimine las columnas antiguas):

```bash
php artisan webnu:migrate-dish-of-day-to-spotlight --dry-run
php artisan webnu:migrate-dish-of-day-to-spotlight
```

2. Luego `php artisan migrate --force`.

En entornos locales que ya migraron a SQLite **sin** `dish_of_day_product_id`, el comando termina sin cambios (comportamiento esperado).

## Rollback

- Restaurar el backup de BD y revertir el código al commit anterior.
- `php artisan migrate:rollback` solo si la migración implementa un `down()` seguro; muchas migraciones recientes solo añaden columnas.

## Comunicación

- Deploy en horario valle; aviso breve si se usa `artisan down`.
- Tras el deploy: mismos logins, mismas cartas y mismos slugs QR.
