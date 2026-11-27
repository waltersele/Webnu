# Staging de Webnu (`staging.webnu.es`)

Guía paso a paso para validar un cambio grande (p. ej. el salto Laravel 10 → 13) en un subdominio de cPanel **antes** de tocar producción. Complementa [deploy.md](./deploy.md) y [MIGRACION-PRODUCCION.md](./MIGRACION-PRODUCCION.md); no las sustituye.

> Regla de oro: staging **nunca** debe poder escribir en producción, enviar correos o cobros reales, ni tocar las pantallas (TVs) de los clientes. Los pasos 2, 5 y 7 existen por eso. No saltes ninguno.

> Lanza siempre `deploy.sh` dentro de `screen` o `tmux`: si se corta el SSH a mitad, el sitio queda en mantenimiento.

> **Orden de trabajo (resumen).** R1 = la rama `deploy/version-agnostic` mergeada en `main` (L10 + script nuevo + `public/index.php`). Las ramas L13 se rebasan sobre R1 (o se crea `release/l13` = R1 + los 3 saltos). En staging se despliega primero R1 (línea base L10) y después `release/l13`. Producción recibe primero R1 y, validado staging, el **mismo SHA** de `release/l13`. Detalle en §8.

## 1. Subdominio y PHP

1. cPanel → **Domains** → crear `staging.webnu.es`. **Document root:** `public_html/staging.webnu.es/public` (igual que producción, que apunta a `.../webnu.es/public`).
2. cPanel → **MultiPHP Manager** → `staging.webnu.es` = **PHP 8.3** (ver el aviso de HTTP 500 en deploy.md si CLI y web difieren).
3. SSL: AutoSSL/Let's Encrypt para el subdominio.

## 2. Base de datos nueva con el dump de producción

1. cPanel → **MySQL Databases**: crear BD y usuario nuevos (p. ej. `wwwwebnu_stg` / `wwwwebnu_stgu`) y asignar todos los privilegios. **No reutilices la BD ni el usuario de producción.**
2. Dump de producción (solo lectura sobre prod) y carga en la BD nueva. **Borra el dump al terminar**: contiene datos reales de clientes.

```bash
mysqldump --single-transaction --quick -u PROD_USER -p PROD_DB | gzip > ~/staging-dump.sql.gz
gunzip -c ~/staging-dump.sql.gz | mysql -u STG_USER -p STG_DB
shred -u ~/staging-dump.sql.gz 2>/dev/null || rm -f ~/staging-dump.sql.gz
```

3. **Neutralizar secretos e integraciones del dump.** La tabla `platform_settings` tiene prioridad sobre el `.env` y guarda SMTP, Stripe, Gemini, TVPik, analítica… Si no la limpias, staging puede enviar correos reales, cobrar con Stripe live o hablar con las TVs de los clientes aunque el `.env` diga otra cosa. Ejecuta **todo** esto en la BD de **staging** (compruébalo dos veces: nunca en producción):

```sql
DELETE FROM platform_settings WHERE `key` IN (
  'mail_host','mail_port','mail_username','mail_password','mail_encryption',
  'stripe_key','stripe_secret','stripe_webhook_secret','gemini_api_key',
  'google_client_id','google_client_secret','google_redirect_uri','google_site_verification',
  'content_connector_secret','pre_alta_ingest_key',
  'tvpik_api_url','tvpik_web_url','tvpik_app_key','digital_signage_app_key',
  'gtag_measurement_id','gtm_container_id','clarity_project_id','meta_pixel_id','linkedin_partner_id',
  'plausible_domain','plausible_upstream_url'
) OR `key` LIKE 'stripe\_price\_%' OR `key` LIKE 'stripe\_product\_%';

INSERT INTO platform_settings (`key`,`value`,created_at,updated_at) VALUES
  ('mail_mailer','log',NOW(),NOW()),
  ('measurement_enabled','0',NOW(),NOW()),
  ('tvpik_stub_screens','1',NOW(),NOW())
ON DUPLICATE KEY UPDATE `value`=VALUES(`value`);

UPDATE users SET tvpik_api_token=NULL, tvpik_connected_at=NULL, tvpik_org_id=NULL, stripe_id=NULL;
DELETE FROM tvpik_screen_links;
```

   Compatibilidad: `VALUES(col)` en `ON DUPLICATE KEY UPDATE` funciona en MySQL y MariaDB; en MySQL 8.0.20+ está marcada como obsoleta y solo emite un aviso. `docs/deploy.md` solo documenta «MySQL/MariaDB» sin versión; si tu servidor es MySQL 8.0.20+ y quieres evitar el aviso puedes usar `INSERT … AS new … UPDATE value=new.value`, pero **MariaDB no lo admite**.

   Las tablas y columnas están comprobadas contra las migraciones del repo: `users.tvpik_api_token`, `tvpik_connected_at`, `tvpik_org_id` (migración `2026_05_21_160000_add_tvpik_integration_tables`), `users.stripe_id` (Cashier), tabla `tvpik_screen_links`, y las claves de `platform_settings` de `PlatformSettingsController`, `PlatformSetting::$encryptedKeys`, `config/billing.php` (`stripe_price_*`, `stripe_product_*`) y `MeasurementSettingsService`.

4. **Verificación (obligatoria).** Revisa a mano lo que queda y borra cualquier cosa que parezca un secreto, una URL de producción o un ID de Stripe/TVPik:

```sql
SELECT `key` FROM platform_settings;
SELECT COUNT(*) FROM tvpik_screen_links;                                  -- 0
SELECT COUNT(*) FROM users WHERE stripe_id IS NOT NULL OR tvpik_api_token IS NOT NULL;  -- 0
```

5. **`APP_KEY`:** genera una **nueva** para staging **antes del primer deploy** (aún no hay `vendor/`, así que no uses `artisan key:generate`) y pégala en el `.env`: `php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"`. Los valores cifrados que pudieran quedar del dump ya no se leerán y no hacen falta (los secretos están borrados). No copies la clave de producción.
6. Los usuarios ya no tienen `stripe_id`: el checkout de test crea clientes nuevos en Stripe test.

## 3. Clonar el repo y preparar el sitio

```bash
ssh wwwwebnu@webnu.es
cd ~
git clone https://USER:ghp_TOKEN@github.com/waltersele/Webnu.git webnu-staging

# Directorios que el rsync no crea (están excluidos)
S=~/public_html/staging.webnu.es
mkdir -p $S/scripts $S/bootstrap/cache $S/storage/{logs,backups,framework/{sessions,cache,views}}
chmod -R 775 $S/storage $S/bootstrap/cache

# El script del servidor NO se actualiza solo (rsync excluye scripts/) y el deploy
# aborta si difiere del del commit destino. Extráelo del commit R1 (el clon todavía no
# está en ese commit, no lo copies del árbol de trabajo):
git -C ~/webnu-staging fetch origin
git -C ~/webnu-staging show <SHA-R1>:scripts/deploy.sh > $S/scripts/deploy.sh && chmod +x $S/scripts/deploy.sh
```

`$HOME/webnu-staging` es el **clon** (`REPO_ROOT`); el sitio vivo (`SITE_ROOT`) es `~/public_html/staging.webnu.es`. Tras el primer deploy (cuando ya exista `vendor/`), enlaza el almacenamiento público: `php artisan storage:link`.

## 4. Copiar `public/img` de producción

Las fotos y logos están en `public/img/` (no van en git ni en el deploy):

```bash
cp -a ~/public_html/webnu.es/public/img ~/public_html/staging.webnu.es/public/img
```

## 5. `.env` de staging

Créalo **en el servidor** (`nano ~/public_html/staging.webnu.es/.env`) partiendo de `.env.example` del repo. **No lo copies desde `C:\webProject\webnu`**: ese fichero tiene BOM y rompe la primera variable.

| Variable | Valor en staging |
|---|---|
| `APP_ENV` | `staging` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://staging.webnu.es` (con `https`) |
| `APP_KEY` | nueva (`php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"`), nunca la de producción |
| `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | los de la BD **nueva** de staging (`DB_HOST` es obligatorio: el backup lo lee) |
| `MAIL_MAILER` | `log` (no se envía nada; los correos quedan en `storage/logs`) |
| `QUEUE_CONNECTION` | `sync` |
| `STRIPE_KEY`, `STRIPE_SECRET` | claves **test** (`pk_test_…`, `sk_test_…`) |
| `STRIPE_WEBHOOK_SECRET` | el `whsec_…` del endpoint de **test** (ver paso 9) |
| `STRIPE_PRICE_*` | IDs de precio **test** (los de live no existen en test) |
| `TVPIK_API_URL` | **vacío** (nunca la URL real de TVPik) |
| `TVPIK_STUB_SCREENS` | `true` |
| `SESSION_DOMAIN` | vacío, o `staging.webnu.es`. Nunca `.webnu.es` (compartiría cookies con producción) |
| `PLAUSIBLE_DOMAIN` | **vacío**; también vacíos `GTAG_MEASUREMENT_ID`, `GTM_CONTAINER_ID`, `CLARITY_PROJECT_ID`, `META_PIXEL_ID`, `LINKEDIN_PARTNER_ID`; `MEASUREMENT_ENABLED=false` |
| `GEMINI_API_KEY` | opcional; solo si vas a probar traducción/escaneo (clave de pruebas) |
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

El script toma como `SITE_ROOT` la carpeta que contiene `scripts/` (si exportas un `SITE_ROOT` distinto, aborta) y **aborta** si el sitio no es producción y `STATE_FILE`/`REPO_ROOT` siguen apuntando a los de producción (y, a la inversa, si el sitio es producción con valores que no son los de producción, salvo `ALLOW_CUSTOM=1`). La configuración de staging va en un fichero del propio sitio, `.deploy.env` (se excluye del rsync). **No se ejecuta con `source`**: solo se leen líneas `CLAVE=valor` con claves permitidas (`REPO_ROOT`, `REPO_BRANCH`, `REPO_REMOTE`, `STATE_FILE`, `LOG_FILE`, `BACKUP_DIR`, `PHP_BIN`, `COMPOSER_BIN`, `SMOKE_MENU_PATH`, `SMOKE_AUTH`); cualquier otra clave se avisa y se ignora. Solo se expanden `$HOME` y `~`; usa comillas simples para valores literales (p. ej. una clave con `$`).

```bash
cat > ~/public_html/staging.webnu.es/.deploy.env <<'EOF'
REPO_ROOT=$HOME/webnu-staging
REPO_BRANCH=main
STATE_FILE=$HOME/.webnu-staging-last-deploy-sha
SMOKE_MENU_PATH=/carta/demo
SMOKE_AUTH='usuario:clave'
EOF
chmod 600 ~/public_html/staging.webnu.es/.deploy.env
```

(`SMOKE_AUTH` es el usuario/clave de Directory Privacy; así no queda en el historial de la shell. `SMOKE_MENU_PATH` debe ser una carta pública que exista en el dump.) Lo que exportes en el entorno manda sobre el fichero, y el script avisa al arrancar de las variables de deploy que encuentre; por eso, **antes de cada deploy**:

```bash
env | grep -E 'SITE_ROOT|REPO_|STATE_FILE|DEPLOY_REF|SKIP_'    # no debe salir nada que no esperes
```

### Orden de despliegue (R1 y `release/l13`)

1. **R1** = `deploy/version-agnostic` mergeada en `main`: Laravel 10 + `scripts/deploy.sh` nuevo + `public/index.php` con el chequeo de mantenimiento.
2. Las ramas L13 se **rebasan sobre R1**, o se crea una rama `release/l13` = R1 + los 3 saltos. Así el `deploy.sh` y el `index.php` de R1 viajan también en L13.
3. En staging, línea base y después L13, siempre por SHA exacto:

```bash
screen -S deploy          # o tmux
cd ~/public_html/staging.webnu.es
DEPLOY_REF=<SHA de R1> ./scripts/deploy.sh --dry-run
DEPLOY_REF=<SHA de R1> ./scripts/deploy.sh --full-rsync            # línea base L10
DEPLOY_REF=<SHA de release/l13> ./scripts/deploy.sh --full-rsync   # lo que queremos validar
```

4. **Producción** recibe primero R1 (con `public/index.php` y `deploy.sh` nuevos) y, una vez validado staging, el **MISMO SHA** de `release/l13` (`DEPLOY_REF=<sha> ./scripts/deploy.sh`), no una rama que pueda haberse movido.

> **No uses `SKIP_SCRIPT_CHECK=1` para saltarte el aviso de «deploy.sh difiere».** Ocurre cuando el destino es anterior al `deploy.sh` nuevo (p. ej. una rama L13 sin rebasar sobre R1): desplegarla sobrescribiría `public/index.php` con la versión vieja, sin el chequeo de mantenimiento, justo en el deploy más delicado. Despliega un ref que ya contenga R1. Si el script del servidor está desactualizado, actualízalo con `git -C <REPO_ROOT> show <SHA>:scripts/deploy.sh > scripts/deploy.sh` (el mensaje del script ya lo indica). En producción, `SKIP_SCRIPT_CHECK` solo se admite con TTY y confirmación escrita.

Con TTY, el script muestra `SITE_ROOT`, `APP_URL`, `REPO_ROOT`, rama o ref, `STATE_FILE` y «en vivo X → destino Y», y pide escribir el host de `APP_URL`; `--yes` se salta la confirmación. En el primer deploy (aún sin `vendor/`) no intenta `artisan down`. Al terminar ejecuta el smoke test (`/`, `/login`, `/register` y `SMOKE_MENU_PATH`); si algo no da 200 (o el sitio sigue en mantenimiento) sale con código 3 y avisa en rojo. Si el deploy falla a medias, el sitio queda en mantenimiento: ver [deploy.md](./deploy.md#rollback-rápido). La salida completa queda en `storage/backups/deploy-<fecha>.out`.

> **Prerrequisito para producción:** el `public/index.php` de R1 sirve la página de mantenimiento pre-renderizada **antes** de cargar `vendor/`. Debe estar en producción **antes** que L13 (por eso R1 va primero). Si no, durante el deploy el modo mantenimiento dependería de un `vendor/` a medias.

## 9. Checklist de validación

```bash
cd ~/public_html/staging.webnu.es
php artisan webnu:deploy-check
php artisan webnu:audit-public-menus --base=https://staging.webnu.es
```

`audit-public-menus` acepta `--base=` (por defecto `APP_URL`), `--csv=`, `--legacy` y `--timeout=`. **No uses `--legacy` en staging** (esos redirects apuntan a `https://webnu.es`). Con Directory Privacy activa dará 401: desactívala un momento o pásale la URL con credenciales (si Guzzle no las aplica, desactiva la protección).

- [ ] `webnu:deploy-check` sin errores críticos.
- [ ] `webnu:audit-public-menus`: todas las cartas en 200.
- [ ] Login con 2 o 3 cuentas reales del dump → `/admin` sin bucle de onboarding.
- [ ] Registro de una cuenta nueva (el correo de bienvenida aparece en `storage/logs`, no se envía).
- [ ] Editar la carta (sección, plato, precio) y comprobarla en `/carta/{slug}`.
- [ ] QR y PDF de la carta (la URL del QR es `https://staging.webnu.es/carta/...`).
- [ ] Traducción con Gemini (si configuraste clave).
- [ ] Subida de foto de plato y de logo (permisos de `public/img`).
- [ ] Checkout de Stripe en modo test con tarjeta `4242 4242 4242 4242` (los usuarios del dump no tienen `stripe_id`; se crean en Stripe test).
- [ ] Webhook de test: crea el endpoint `https://staging.webnu.es/stripe/webhook` en Stripe (modo test) y copia su `whsec_…` al `.env`; con Directory Privacy desactivada, lanza el evento desde el dashboard o con `stripe trigger checkout.session.completed`, y confirma 200 en el dashboard y el efecto en la BD. Vuelve a activar la protección.
- [ ] Panel admin / plataforma (`/admin`, Plataforma → Configuración) carga sin errores.
- [ ] TVPik **solo en modo stub** (`TVPIK_STUB_SCREENS=true`, sin URL real): pantallas simuladas y pricing. **No** pruebes «connect» real contra TVPik: tocaría las TVs de clientes reales.
- [ ] `tail -n 100 storage/logs/laravel.log`: sin errores nuevos respecto a producción.

## 10. Ensayo del rollback (obligatorio antes de producción)

Con L13 desplegado y validado:

```bash
cd ~/public_html/staging.webnu.es
./scripts/deploy.sh --rollback
```

Muestra «en vivo X → destino Y» (el destino sale de `.rollback-target`/`.pending`/`.prev`, no de una heurística; `ROLLBACK_TO=<sha>` tiene prioridad), valida la instantánea antes de tocar nada, y después restaura `vendor/`, `composer.json` y `composer.lock` desde `storage/backups/vendor-<SHA>.tar.gz` (si no existe, hace `composer install` con el lock de ese SHA en un directorio temporal), comprueba que el major de Laravel coincide con el lock, regenera cachés, ejecuta `webnu:deploy-check` y el smoke test. **No restaura la BD** y las migraciones nuevas **no se revierten solas**. Si el rollback se interrumpe, relánzalo: reutiliza el destino guardado.

Comprueba:

- [ ] Salida: `Laravel VERSION=10.x … coincide con composer.lock` y smoke test en verde.
- [ ] Login y una carta pública funcionan **con el esquema de BD ya migrado a L13**. Si no, ensaya también el rescate de BD (comando en deploy.md).
- [ ] Vuelve a desplegar `release/l13` (`DEPLOY_REF=<sha>`) y comprueba que sigue sano.

Para llevar a producción exactamente lo validado: `DEPLOY_REF=<sha de release/l13> ./scripts/deploy.sh` (con backup propio, `--dry-run` previo y ventana de bajo uso).

## 11. Ensayos de fallo a propósito (antes de producción)

Provoca estos fallos **en staging** para ver que el script se comporta como se espera y que sabes recuperarte:

- [ ] **`composer install` roto:** crea una rama de prueba basada en `release/l13` con un `composer.lock` corrupto (p. ej. quitando una llave del JSON) y despliégala con `DEPLOY_REF`. Esperado: falla al preparar `vendor/` **sin tocar el sitio**, que sigue arriba con L13; no queda `.vendor-new-*`.
- [ ] **`migrate` fallido:** rama de prueba con una migración que lance una excepción. Esperado: el sitio **queda en mantenimiento** con el banner rojo, existe `~/.webnu-staging-last-deploy-sha.pending` y `--rollback` vuelve al último SHA bueno.
- [ ] **Rollback interrumpido y relanzado:** durante `--rollback` corta el proceso (Ctrl+C o cierra el SSH, de ahí `screen`/`tmux`) tras el `git reset`; relanza `--rollback` y comprueba que muestra «rollback anterior interrumpido (.rollback-target)» y llega al mismo destino.
- [ ] Tras cada ensayo, deja staging sano (rollback o redeploy) y confirma smoke test en verde.
