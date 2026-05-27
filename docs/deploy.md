# Despliegue de Webnu en producción

Guía operativa del despliegue de **webnu.es** desde tu equipo a producción.

> Para la guía histórica con foco en **migraciones de BD sin pérdida de datos**, mira [docs/deploy-migrations.md](./deploy-migrations.md). Este documento es el **flujo del día a día**.

## Arquitectura del hosting

```
/home/wwwwebnu/
├── webnu-deploy/                        ← clon git de origin/main (NO accesible vía web)
│   └── .git/, app/, public/, ...
└── public_html/
    ├── .well-known/                     (SSL/Let's Encrypt — no tocar)
    ├── app/                             (otro subdominio, ignorar)
    ├── cgi-bin/
    └── webnu.es/                        ← APLICACIÓN LIVE
        ├── app/                         ← código del proyecto
        ├── bootstrap/  config/  database/  resources/  routes/
        ├── public/                      ← document root público de webnu.es
        │   └── img/                     ← uploads de clientes (NO sobreescribir)
        ├── storage/
        ├── vendor/                      (instalado con PHP 7.4, no se sincroniza)
        ├── .env                         (credenciales BD, Stripe, Gemini, mail)
        └── scripts/deploy.sh            ← este script
```

- **Document root** del dominio en cPanel: `/home/wwwwebnu/public_html/webnu.es/public/`.
- **Stack actual**: PHP 8.3, Laravel 10.x, MySQL/MariaDB (cPanel), Cashier 15.
- **Rama desplegada**: `origin/main`.
- **Nota**: el despliegue **SIEMPRE** respeta `.env`, `public/img/` (uploads) y `storage/`.

## Despliegue normal (el caso del 95% de las veces)

Desde tu equipo:

```bash
git add .
git commit -m "feat: cambios"
git push origin main
```

Conéctate por SSH al servidor y lanza el script:

```bash
ssh wwwwebnu@webnu.es
cd /home/wwwwebnu/public_html/webnu.es
./scripts/deploy.sh
```

El script hace:

1. `git fetch + reset --hard origin/main` en `~/webnu-deploy/`.
2. Calcula la lista de **archivos cambiados** entre el SHA anterior y el nuevo (sin transferir el repo completo).
3. Modo mantenimiento (`artisan down`).
4. Backup MySQL automático en `storage/backups/db-YYYYMMDD-HHMM.sql.gz`.
5. `rsync` solo de los ficheros cambiados, excluyendo `.env`, `public/img/`, `storage/`, etc.
6. Limpia `bootstrap/cache/{services,packages,config}.php`.
7. En PHP 8.x/Laravel 10: `composer install --no-dev` (instala/actualiza `vendor/`). En stacks legacy, hace `composer dump-autoload`.
8. `php artisan package:discover`.
9. `php artisan migrate --force`.
10. Si el commit incluye `database/seeds/ProductionDemoSeeder.php` ⇒ lo ejecuta.
11. Si el commit incluye `app/Console/Commands/SeedDemosCommand.php` ⇒ ejecuta `webnu:seed-demos`.
12. `view:clear` + `config:cache`.
13. Guarda el nuevo SHA en `~/.webnu-last-deploy-sha` y registra el deploy en `storage/backups/deploys.log`.
14. `artisan up`.

Salida típica:

```
→ Sincronizando ~/webnu-deploy con origin/main
→ PREV=aa12e8b  NEW=b3f9d24
→ Backup MySQL
✓ Backup en storage/backups/db-20260525-1430.sql.gz (12M)
→ Calculando archivos cambiados entre aa12e8b y b3f9d24
→ Archivos a copiar: 7  /  a borrar: 0
→ Limpiando bootstrap cache
→ Regenerando autoload
→ Re-descubriendo paquetes
→ Aplicando migraciones
→ Refrescando cachés
✓ Despliegue completo  aa12e8b → b3f9d24
```

## Flags útiles

| Flag | Cuándo |
|---|---|
| `--dry-run` | Muestra todo lo que haría sin tocar el sitio. Recomendado la 1ª vez del día. |
| `--rollback` | Vuelve al SHA del despliegue anterior. **No restaura BD** automáticamente (ver sección). |
| `--skip-migrate` | Saltar `migrate`. Útil si solo cambian vistas/JS. |
| `--skip-backup` | Saltar `mysqldump`. Útil para deploys triviales repetidos. |
| `--no-down` | Deploy en caliente (sin `artisan down`). Riesgo si hay migraciones. |
| `--full-rsync` | rsync completo en lugar de delta git. Útil si sospechas archivos desincronizados. |
| `--with-vendor` | Fuerza sincronizar `composer.json/lock` y `vendor/` + ejecutar `composer install` (útil en upgrades). |

## Rollback rápido

Imagina que tras un deploy notas un fallo grave:

```bash
ssh wwwwebnu@webnu.es
cd /home/wwwwebnu/public_html/webnu.es
./scripts/deploy.sh --rollback
```

Esto revierte el código al SHA anterior. **La BD no se restaura automáticamente** porque las migraciones pueden ser destructivas y no siempre tienen `down()` correcto. Si el problema fue una migración, restaura el dump manualmente:

```bash
gunzip -c storage/backups/db-YYYYMMDD-HHMM.sql.gz \
  | mysql -h 127.0.0.1 -u DB_USER -p'DB_PASS' DB_NAME
```

> Identifica el dump usando `ls -lt storage/backups/db-*.sql.gz | head -5`.

## Primera vez en un servidor nuevo

```bash
ssh wwwwebnu@webnu.es

# 1) Clonar el repo (con Personal Access Token si es privado)
cd /home/wwwwebnu
git clone https://USER:ghp_TOKEN@github.com/waltersele/Webnu.git webnu-deploy

# 2) Copiar el script al sitio
cp /home/wwwwebnu/webnu-deploy/scripts/deploy.sh \
   /home/wwwwebnu/public_html/webnu.es/scripts/deploy.sh
chmod +x /home/wwwwebnu/public_html/webnu.es/scripts/deploy.sh

# 3) Probar en dry-run
cd /home/wwwwebnu/public_html/webnu.es
./scripts/deploy.sh --dry-run --full-rsync
```

## Qué NUNCA debe pisar el deploy

El script ya los excluye, pero es bueno conocerlos:

| Archivo / carpeta | Por qué |
|---|---|
| `.env` | Credenciales de BD, Stripe, Gemini, mail. Cargarse esto = romper el sitio entero. |
| `public/img/` | Fotos subidas por los clientes (logos, productos). Pérdida irreversible. |
| `public/.htaccess` | Redirects de hosting (https, www, etc.). Si lo pisas, posible bucle 301 o caída. |
| `storage/logs/`, `storage/framework/sessions/` | Sesiones activas (logout masivo) y logs en curso. |
| `public/img/` | Ya arriba: uploads de clientes. Nunca sobreescribir. |
| `.env` | Ya arriba: credenciales. Nunca sobreescribir. |

## Variables de entorno a vigilar (.env)

- `APP_ENV=production`, `APP_DEBUG=false`.
- `APP_URL=https://webnu.es` (**importante**: si se queda en `http://` el panel puede cargar assets por HTTP y el navegador los bloqueará → CSS roto).
- `DB_*` correctos.
- `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET` activos.
- `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` (los emails de suscripción los usan).
- `SUPER_ADMIN_EMAILS` con los correos del equipo.
- `GEMINI_API_KEY` o configurado desde Plataforma → Escaneo IA.

## Troubleshooting

### El panel se ve “sin CSS” / logo gigante

Causa típica: `APP_URL` en `http://...` → el navegador bloquea CSS/JS por *mixed content*.

Solución rápida:

```bash
cd /home/wwwwebnu/public_html/webnu.es
nano .env  # asegurar APP_URL=https://webnu.es
/opt/alt/php83/usr/bin/php artisan config:clear
/opt/alt/php83/usr/bin/php artisan view:clear
/opt/alt/php83/usr/bin/php artisan cache:clear
/opt/alt/php83/usr/bin/php artisan config:cache
```

### `Command "webnu:X" is not defined`

El archivo del comando no llegó al server o el autoload no se regeneró.

```bash
ls app/Console/Commands/<Comando>.php
rm -f bootstrap/cache/services.php bootstrap/cache/packages.php
composer dump-autoload --no-scripts --optimize --no-interaction --ignore-platform-reqs
php artisan package:discover
```

### `LogicException: Unable to prepare route [...] for serialization. Uses Closure.`

Hay una ruta definida con `function() { ... }` en lugar de un controlador. **No uses `route:cache`** hasta convertir esa closure en `Route::redirect()` o en un método de controlador.

### `could not find driver` (SQLite/MySQL)

Falta extensión PHP. Verifica con:

```bash
php -m | grep -iE 'pdo|mysql|sqlite'
```

En cPanel: **Software ▸ Select PHP Version**, marca `pdo_mysql` (o `pdo_sqlite`) y guarda.

### Tras el deploy, los assets se ven raros

El navegador puede cachear CSS/JS antiguos.

```bash
# En tu local: añade un parámetro de versión o
# fuerza limpieza de view cache en producción
php artisan view:clear
```

### Mantenimiento bloqueado (`down` no se quita)

```bash
php artisan up
# Si sigue bloqueado:
rm -f storage/framework/down
```

## CI/CD (próximo paso)

Cuando quieras eliminar el paso manual de SSH, configuraremos un workflow `.github/workflows/deploy.yml` que en cada `push origin main` se conecte por SSH y ejecute `./scripts/deploy.sh`. Está documentado para esa fase en una sección futura de este archivo.

## Logs e histórico de despliegues

- Log de cada deploy: `storage/backups/deploys.log`.
- Último SHA desplegado: `~/.webnu-last-deploy-sha`.
- Dumps de BD: `storage/backups/db-*.sql.gz` (rotación manual recomendada cada 30 días).
