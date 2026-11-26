# Staging de Webnu (`staging.webnu.es`)

Guía paso a paso para validar un cambio grande (p. ej. el salto Laravel 10 → 13) en un subdominio de cPanel **antes** de tocar producción. Complementa [deploy.md](./deploy.md) y [MIGRACION-PRODUCCION.md](./MIGRACION-PRODUCCION.md); no las sustituye.

> Regla de oro: staging **nunca** debe poder escribir en producción ni enviar correos o cobros reales. Los pasos 4, 6 y 7 existen por eso. No saltes ninguno.

## 1. Subdominio y PHP

1. cPanel → **Domains** → crear `staging.webnu.es`. **Document root:** `public_html/staging.webnu.es/public` (igual que producción, que apunta a `.../webnu.es/public`).
2. cPanel → **MultiPHP Manager** → `staging.webnu.es` = **PHP 8.3** (ver el aviso de HTTP 500 en deploy.md si CLI y web difieren).
3. SSL: AutoSSL/Let's Encrypt para el subdominio.

## 2. Base de datos nueva con el dump de producción

1. cPanel → **MySQL Databases**: crear BD y usuario nuevos (p. ej. `wwwwebnu_stg` / `wwwwebnu_stgu`) y asignar todos los privilegios. **No reutilices la BD ni el usuario de producción.**
2. Dump de producción (o el último de `storage/backups/db-*.sql.gz`) y carga en la BD nueva:

```bash
# En el servidor (ajusta los datos de PRODUCCIÓN; solo lectura sobre prod)
mysqldump --single-transaction --quick -u PROD_USER -p PROD_DB | gzip > ~/staging-dump.sql.gz
gunzip -c ~/staging-dump.sql.gz | mysql -u STG_USER -p STG_DB
```

3. **Neutralizar la configuración de plataforma del dump.** La tabla `platform_settings` tiene prioridad sobre el `.env` y guarda correo SMTP, Stripe, Gemini, etc. Si no la limpias, staging puede enviar correos reales o usar Stripe live aunque pongas `MAIL_MAILER=log`:

```sql
UPDATE platform_settings SET value = NULL WHERE `key` IN (
  'mail_host','mail_port','mail_username','mail_password','mail_encryption',
  'stripe_key','stripe_secret','stripe_webhook_secret',
  'gemini_api_key','google_client_id','google_client_secret',
  'content_connector_secret','pre_alta_ingest_key','tvpik_app_key','digital_signage_app_key',
  'gtag_measurement_id','gtm_container_id','clarity_project_id','meta_pixel_id','linkedin_partner_id'
);
INSERT INTO platform_settings (`key`,`value`) VALUES ('mail_mailer','log')
  ON DUPLICATE KEY UPDATE `value`='log';
UPDATE platform_settings SET value='0' WHERE `key`='measurement_enabled';
```

   (Si alguna columna `created_at/updated_at` es obligatoria en el `INSERT`, añádela con `NOW()`.) Revisa después con `SELECT `key`, value IS NOT NULL FROM platform_settings;`.

4. **`APP_KEY`:** los valores cifrados del dump solo se descifran con la `APP_KEY` de producción. Si copias esa clave a staging (necesario para que `platform_settings` y las sesiones del dump sean legibles), tras el paso anterior ya no queda ningún secreto real dentro. Si prefieres una clave nueva, los valores cifrados no se leerán (da igual: los has puesto a NULL) y tendrás que volver a introducir lo que necesites.
5. Los usuarios del dump tienen `stripe_id` de **live**. Con claves test, Stripe no los conoce. Para probar el checkout usa una cuenta nueva creada en staging (o `UPDATE users SET stripe_id = NULL` en staging).

## 3. Clonar el repo y preparar el sitio

```bash
ssh wwwwebnu@webnu.es
cd ~
# Clon del repo (mismo token que en deploy.md si es privado)
git clone https://USER:ghp_TOKEN@github.com/waltersele/Webnu.git webnu-staging

# Directorios que el rsync no crea (están excluidos)
S=~/public_html/staging.webnu.es
mkdir -p $S/scripts $S/bootstrap/cache $S/storage/{logs,backups,framework/{sessions,cache,views}}
chmod -R 775 $S/storage $S/bootstrap/cache

# El script del servidor NO se actualiza solo (rsync excluye scripts/): cópialo a mano
# cada vez que cambie en el repo
cp ~/webnu-staging/scripts/deploy.sh $S/scripts/deploy.sh && chmod +x $S/scripts/deploy.sh
```

`$HOME/webnu-staging` es el **clon** (`REPO_ROOT`); el sitio vivo (`SITE_ROOT`) es `~/public_html/staging.webnu.es`.

## 4. Copiar `public/img` de producción

Las fotos y logos están en `public/img/` (no van en git ni en el deploy):

```bash
mkdir -p ~/public_html/staging.webnu.es/public
cp -a ~/public_html/webnu.es/public/img ~/public_html/staging.webnu.es/public/img
```

## 5. `.env` de staging

Créalo **en el servidor** (`nano ~/public_html/staging.webnu.es/.env`) partiendo de `.env.example` del repo. **No lo copies desde `C:\webProject\webnu`**: ese fichero tiene BOM y rompe la primera variable.

| Variable | Valor en staging |
|---|---|
| `APP_ENV` | `staging` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://staging.webnu.es` (con `https`, ver Troubleshooting de deploy.md) |
| `APP_KEY` | la de producción si quieres leer datos cifrados del dump (ver 2.4), o `php artisan key:generate` |
| `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | los de la BD **nueva** de staging (`DB_HOST` es obligatorio: el backup lo lee) |
| `MAIL_MAILER` | `log` (no se envía nada; los correos quedan en `storage/logs`) |
| `QUEUE_CONNECTION` | `sync` |
| `STRIPE_KEY`, `STRIPE_SECRET` | claves **test** (`pk_test_…`, `sk_test_…`) |
| `STRIPE_WEBHOOK_SECRET` | el `whsec_…` del endpoint de **test** (ver paso 9) |
| `STRIPE_PRICE_*` | IDs de precio **test** (los de live no existen en test) |
| `SESSION_DOMAIN` | vacío, o `staging.webnu.es`. Nunca `.webnu.es` (compartiría cookies con producción) |
| `PLAUSIBLE_DOMAIN`, `GTAG_MEASUREMENT_ID`, `GTM_CONTAINER_ID`, `CLARITY_PROJECT_ID`, `META_PIXEL_ID`, `LINKEDIN_PARTNER_ID` | vacíos; `MEASUREMENT_ENABLED=false` |
| `GEMINI_API_KEY` | opcional; solo si vas a probar traducción/escaneo (usa una clave de pruebas) |
| `SUPER_ADMIN_EMAILS` | tu correo |

## 6. Bloquear indexación y acceso

1. En el `.htaccess` del subdominio (`~/public_html/staging.webnu.es/public/.htaccess`; el deploy **no** lo toca) parte de `public/.htaccess` del repo y:
   - **Borra** las líneas `redirect 301 …` hacia `https://webnu.es/…` y el bloque `RewriteCond %{HTTP_HOST} ^webnu\.es` (son de producción).
   - Añade arriba:

```apache
<IfModule mod_headers.c>
    Header set X-Robots-Tag "noindex, nofollow, noarchive"
</IfModule>
```

2. cPanel → **Directory Privacy** → carpeta `public_html/staging.webnu.es/public` → protegida con contraseña (crea usuario/clave).
3. Comprueba: `curl -I https://staging.webnu.es/` → `401`; con `-u usuario:clave` → `200` y cabecera `X-Robots-Tag`.

> La protección con contraseña también bloquea a Stripe: el webhook de staging recibirá 401. Para probarlo, desactiva Directory Privacy unos minutos (paso 9). Y `webnu:audit-public-menus` desde el servidor también recibirá 401 mientras esté activa.

## 7. Cron y colas: nada que envíe correos

- **No crees** el cron de `schedule:run` en staging (envía recordatorios y expira trials). Si lo necesitas para probar el scheduler, hazlo con `MAIL_MAILER=log` y bórralo después.
- `QUEUE_CONNECTION=sync`: no hay workers que arrancar.
- Revisa `crontab -l` y cPanel → Cron Jobs: no debe haber nada apuntando a `staging.webnu.es`.

## 8. Desplegar en staging

Variables importantes: **`STATE_FILE` debe ser distinto del de producción** (por defecto es `~/.webnu-last-deploy-sha`, compartido). `LOG_FILE` y `BACKUP_DIR` salen de `SITE_ROOT`.

Orden recomendado (así el ensayo de rollback tiene una línea base L10):

```bash
cd ~/public_html/staging.webnu.es

export SITE_ROOT=$HOME/public_html/staging.webnu.es
export REPO_ROOT=$HOME/webnu-staging
export STATE_FILE=$HOME/.webnu-staging-last-deploy-sha
export SMOKE_AUTH='usuario:clave'        # el de Directory Privacy
export SMOKE_MENU_PATH=/carta/demo       # una carta pública que exista en el dump

# a) Línea base: producción actual (L10)
REPO_BRANCH=main ./scripts/deploy.sh --full-rsync

# b) Lo que queremos validar (L13)
REPO_BRANCH=l13/paso-3-laravel-13 ./scripts/deploy.sh --full-rsync
```

Si dudas, añade `--dry-run` para ver qué haría sin tocar nada. Al terminar ejecuta el smoke test (`/`, `/login`, `/register` y `SMOKE_MENU_PATH`); si algo no da 200 sale con código 3 y avisa en rojo (no hace rollback solo). Si el deploy falla a medias el sitio queda en mantenimiento: ver [deploy.md](./deploy.md#rollback-rápido).

## 9. Checklist de validación

```bash
cd ~/public_html/staging.webnu.es
php artisan webnu:deploy-check
php artisan webnu:audit-public-menus --base=https://staging.webnu.es
```

`audit-public-menus` acepta `--base=` (por defecto `APP_URL`), `--csv=`, `--legacy` y `--timeout=`. **No uses `--legacy` en staging** (esos redirects apuntan a `https://webnu.es`). Con Directory Privacy activa dará 401: desactívala un momento o pásale la URL con credenciales (`--base=https://usuario:clave@staging.webnu.es`; si Guzzle no las aplica, desactiva la protección).

- [ ] `webnu:deploy-check` sin errores críticos.
- [ ] `webnu:audit-public-menus`: todas las cartas en 200.
- [ ] Login con 2 o 3 cuentas reales del dump → `/admin` sin bucle de onboarding.
- [ ] Registro de una cuenta nueva (el correo de bienvenida aparece en `storage/logs`, no se envía).
- [ ] Editar la carta (sección, plato, precio) y comprobarla en `/carta/{slug}`.
- [ ] QR y PDF de la carta (la URL del QR es `https://staging.webnu.es/carta/...`).
- [ ] Traducción con Gemini (si configuraste clave).
- [ ] Subida de foto de plato y de logo (permisos de `public/img`).
- [ ] Checkout de Stripe en modo test con tarjeta `4242 4242 4242 4242`, con una cuenta nueva (ver 2.5).
- [ ] Webhook de test: crea el endpoint `https://staging.webnu.es/stripe/webhook` en Stripe (modo test) y copia su `whsec_…` al `.env`; con Directory Privacy desactivada, lanza el evento desde el dashboard o con `stripe trigger checkout.session.completed`, y confirma 200 en el dashboard de Stripe y el efecto en la BD. Vuelve a activar la protección.
- [ ] Panel admin / plataforma (`/admin`, Plataforma → Configuración) carga sin errores.
- [ ] TVPik (conexión `integrations/tvpik/connect` y pantalla de pricing) si está activo en el dump.
- [ ] `tail -n 100 storage/logs/laravel.log`: sin errores nuevos respecto a producción.

## 10. Ensayo del rollback (obligatorio antes de producción)

Con L13 desplegado y validado:

```bash
cd ~/public_html/staging.webnu.es
./scripts/deploy.sh --rollback
```

Esto vuelve al SHA anterior, restaura `vendor/`, `composer.json` y `composer.lock` desde `storage/backups/vendor-<SHA>.tar.gz` (si no existe, hace `composer install` con el lock de ese SHA), comprueba que el major de Laravel coincide con el lock y regenera cachés. **No restaura la BD** y las migraciones nuevas **no se revierten solas**.

Comprueba:

- [ ] Salida: `Laravel VERSION=10.x … coincide con composer.lock`.
- [ ] `curl -u usuario:clave -I https://staging.webnu.es/`, `/login`, `/register` → 200.
- [ ] Login y una carta pública funcionan **con el esquema de BD ya migrado a L13**. Si no, ensaya también el rescate de BD: restaurar `storage/backups/db-*.sql.gz` (comando en deploy.md).
- [ ] Vuelve a desplegar L13 (`REPO_BRANCH=l13/paso-3-laravel-13 ./scripts/deploy.sh`) y comprueba que sigue sano.

Solo cuando todo lo anterior esté en verde, repite el mismo orden en producción (backup propio, `--dry-run`, ventana de bajo uso).
