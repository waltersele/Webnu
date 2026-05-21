# Migración a producción (webnu.es)

Guía operativa para desplegar la rama actual **en el mismo servidor** sin perder cartas ni URLs de QR.

## Restricciones

| Qué conservar | Detalle |
|---------------|---------|
| URL pública | `https://webnu.es/carta/{slug}` — los QR impresos codifican esta ruta |
| `companies.slug` | No renombrar en masa; si cambia, añadir 301 en `.htaccess` |
| `public/img/` | Logos, fotos de platos, PDF de carta |
| Redirects legacy | Primeras líneas de `public/.htaccess` — ver [`.htaccess.production-redirects`](../public/.htaccess.production-redirects) |
| **Carta publicada** | Tablas `sections`, `products`, `product_allergens` + fotos en `public/img/` |
| **Stripe** | Opcional en este deploy: en prod **no hay suscripciones activas**; el cobro es por otros medios |

## Situación actual en producción

- **Lo crítico:** cartas en `/carta/{slug}`, secciones, platos e imágenes ya publicados (QR de clientes).
- **Cobro:** fuera de Stripe (transferencia, etc.). No bloquea la migración si Stripe está vacío o el webhook no está configurado aún.
- **Tras el deploy:** podéis volver a montar suscripciones en Stripe cuando queráis ([PLATFORM-BILLING.md](PLATFORM-BILLING.md)). Hasta entonces, el plan del panel lo marca `users.plan` (por defecto `free` tras migrar).

### Clientes que pagáis por otros medios

Tras migrar, si un negocio debe tener Plus/Ilimitado sin Stripe, asignad plan en BD (o desde panel plataforma cuando lo tengáis):

```sql
-- Ejemplo: lista de emails que pagáis manualmente
UPDATE users SET plan = 'plus' WHERE email IN ('cliente1@...', 'cliente2@...');
```

No hace falta tocar `companies.slug` ni reimprimir QR.

Prioridad de verificación tras el deploy:

1. **Cada slug** del inventario → carta pública con secciones y platos visibles
2. Login de 2–3 cuentas reales → **Mi carta** con el mismo contenido que antes
3. Stripe / webhook → solo cuando reactivéis cobro automático

## Herramientas en el repo

| Herramienta | Uso |
|-------------|-----|
| `php artisan webnu:export-companies-inventory --with-users` | CSV en `storage/migration-inventory/` |
| `scripts/export-production-inventory.sql` | Consultas SQL en MySQL (phpMyAdmin / CLI) |
| `scripts/backup-production.ps1` | Backup MySQL + `public/img` + `.htaccess` + `.env` |
| `scripts/rehearse-migration-local.ps1` | Ensayo local tras importar dump |
| `scripts/deploy-production.ps1` | Secuencia de deploy en prod |
| `php artisan webnu:audit-public-menus` | Comprueba HTTP 200 por slug (+ `--legacy`) |
| `php scripts/audit-menu-urls.php` | Mismo audit sin recordar nombre Artisan |

## Fase 0 — Inventario (producción)

```bash
php artisan webnu:export-companies-inventory --with-users
```

O en MySQL:

```bash
mysql -u USER -p DATABASE < scripts/export-production-inventory.sql > inventario.txt
```

Comprobar `APP_URL=https://webnu.es` en `.env` de producción.

Revisar columnas `sections_count` y `products_count` en el CSV: negocios con platos publicados deben tener números > 0 (salvo cartas solo PDF).

Anotar en un aparte los emails que pagáis por otros medios; tras el deploy les pondréis `plan = plus` si hace falta.

## Fase 1 — Backup

En el servidor Windows/Linux:

```powershell
.\scripts\backup-production.ps1 -ProjectRoot "C:\ruta\al\proyecto"
```

Resultado en `storage/backups/pre-migration-FECHA/`.

## Fase 2 — Ensayo local (obligatorio sin staging)

1. Copiar `database-*.sql` → `storage/backups/import/webnu-prod.sql`
2. Importar en MySQL local y ajustar `.env` (`DB_*`, `APP_URL`)
3. Fusionar `public/img` del backup
4. `.\scripts\rehearse-migration-local.ps1`
5. `php artisan serve` y revisar `/admin` con 2–3 cuentas del dump

## Fase 3 — Despliegue

```powershell
.\scripts\deploy-production.ps1
```

**Excluir al subir código:** `.env`, `public/img/`, `storage/` (logs/sesiones).

**Fusionar** `public/.htaccess`: mantener redirects del snippet `.htaccess.production-redirects` + reglas Laravel.

Migraciones 2026: añaden columnas/tablas; usuarios existentes quedan con `onboarding_completed_at` (no van al wizard).

**Stripe:** no es bloqueante si no hay suscripciones activas. Dejad las claves `STRIPE_*` del `.env` de prod como están (o vacías hasta reconfigurar). Cuando activéis cobro de nuevo, configurad webhook y precios según [PLATFORM-BILLING.md](PLATFORM-BILLING.md).

## Fase 4 — Verificación post-migración

```bash
php artisan webnu:export-companies-inventory --with-users
php artisan webnu:audit-public-menus --base=https://webnu.es --csv=storage/migration-inventory/companies-XXXX.csv --legacy
```

Checklist manual:

- [ ] Cada slug del CSV → HTTP 200 en `/carta/{slug}`
- [ ] Logos/PDF sin 404 en `/img/...`
- [ ] 2–3 redirects legacy (`--legacy`)
- [ ] En 3–5 cartas muestra: **secciones y platos** con nombres y precios (no carta vacía)
- [ ] Login clientes → `/admin` sin bucle onboarding; **Mi carta** coincide con lo público
- [ ] Clientes que pagáis manualmente: `users.plan` = `plus` o `unlimited` si deben más límites (escaneos, vídeos)
- [ ] QR PDF con URL `https://webnu.es/carta/...`
- [ ] (Opcional) Stripe/webhook cuando reactivéis suscripciones

## Rollback

1. `php artisan down`
2. Restaurar dump MySQL del backup
3. Restaurar `public/img` y `.htaccess` si se tocaron
4. Código anterior (git tag / zip)
5. `php artisan up`

Evitar `migrate:rollback` en producción salvo emergencia documentada.

## Staging permanente (recomendado tras el primer deploy)

- Dump mensual de MySQL + copia de `public/img`
- Entorno separado (subdominio o máquina local) con el mismo `.env` de prueba
- Antes de cada release: `rehearse-migration-local.ps1` + `webnu:audit-public-menus`

## Plantilla CSV (columnas export)

`id`, `name`, `slug`, `menu_type`, `enabled`, `template`, `user_id`, `public_url`, `sections_count`, `products_count`, `logo_path`, `logo_exists`, `pdf_path`, `pdf_exists`

Usar `public_url` como referencia del QR; tras migración debe coincidir con la URL live.
