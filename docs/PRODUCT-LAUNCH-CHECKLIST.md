# Checklist de lanzamiento — Webnu

Copiar y marcar en cada release mayor. Guía completa: [PRODUCT-LAUNCH-BASE.md](PRODUCT-LAUNCH-BASE.md). Despliegue: [deploy.md](deploy.md).

**Proyecto:** Webnu  
**Dominio:** webnu.es  
**Fecha go-live:** ____________________

---

## A. Infraestructura y dominio

- [ ] DNS apuntando al servidor
- [ ] HTTPS activo (`APP_URL=https://webnu.es`)
- [ ] `APP_ENV=production`, `APP_DEBUG=false`
- [ ] `APP_KEY` generada y respaldada
- [ ] MySQL configurado (no SQLite en producción)
- [ ] `QUEUE_CONNECTION=database`
- [ ] Cron `schedule:run` cada minuto
- [ ] Worker `queue:work` en ejecución continua
- [ ] Backups de base de datos programados
- [ ] `SESSION_SECURE_COOKIE=true`

---

## B. Despliegue de código

- [ ] `git push origin main`
- [ ] `./scripts/deploy.sh` en servidor (o rsync manual)
- [ ] `composer install --no-dev --optimize-autoloader`
- [ ] `php artisan migrate --force`
- [ ] `php artisan config:cache`
- [ ] `php artisan route:cache`
- [ ] `php artisan view:cache`
- [ ] `php artisan queue:restart`
- [ ] `php artisan webnu:deploy-check` sin errores críticos

---

## C. Integraciones operativas (Admin → Plataforma → Configuración)

### Pagos
- [ ] Stripe publishable + secret + webhook secret configurados
- [ ] Webhook Stripe apunta a `https://webnu.es/stripe/webhook`
- [ ] Precios en `/admin/platform/billing`
- [ ] Pago de prueba (modo test o live según fase)

### Email
- [ ] SMTP configurado (host, puerto, usuario, contraseña)
- [ ] SPF/DKIM del dominio de envío
- [ ] Email transaccional de prueba recibido (botón en admin)

### OAuth
- [ ] Google Client ID + Secret en admin
- [ ] `GOOGLE_REDIRECT_URI` = `https://webnu.es/auth/google/callback`
- [ ] URI autorizada en Google Cloud Console
- [ ] Login con Google probado

### IA (Gemini)
- [ ] `gemini_api_key` configurada
- [ ] Botón «Probar Gemini» OK
- [ ] Escaneo de carta en onboarding probado

### TVPik (si aplica)
- [ ] `tvpik_api_url`, `tvpik_app_key` configurados
- [ ] Reproductor `/tv/{slug}?player=1` probado
- [ ] Conexión hub TVPik probada (o modo white-label sin hub)

### Pre-Alta (si aplica)
- [ ] `pre_alta_ingest_key` configurada
- [ ] Ingest externo probado

---

## D. Medición y Search Console

- [ ] Medición activada en admin (`measurement_enabled`)
- [ ] Meta `google_site_verification` en admin
- [ ] Propiedad verificada en Search Console
- [ ] GA4 (`G-...`) o GTM (`GTM-...`) configurado
- [ ] Clarity (opcional)
- [ ] Banner de cookies visible en landing
- [ ] Scripts de analítica solo cargan tras aceptar cookies
- [ ] Cartas públicas `/carta/*` sin analytics de plataforma

---

## E. Identidad visual y assets

### Panel Admin → Marca

- [ ] Logo horizontal subido
- [ ] Isotipo subido
- [ ] Favicon subido
- [ ] Imagen Open Graph **1200×630**
- [ ] `theme_color` del manifest = `#004ac6`

### Archivos en `public/`

- [ ] `img/brand/logo.*`, `isotipo.*`, `favicon.*`, `og.*`
- [ ] `img/pwa/icon-192.png`, `icon-512.png`, `icon-512-maskable.png`
- [ ] `manifest.webmanifest` y `manifest-admin.webmanifest`
- [ ] `webnu:deploy-check` sin avisos de iconos PWA

### Comprobaciones visuales

- [ ] Favicon visible en pestaña
- [ ] Logo correcto en landing, login y emails
- [ ] Compartir URL landing → imagen OG correcta
- [ ] PWA instalable en móvil (Lighthouse)

---

## F. SEO técnico

- [ ] `https://webnu.es/robots.txt` accesible y correcto
- [ ] `https://webnu.es/sitemap.xml` responde **200**
- [ ] Sitemap enviado en Search Console
- [ ] Landing: canonical, og:title, og:image, description
- [ ] Páginas legales: `/legal/privacidad`, `/legal/terminos`
- [ ] Rutas privadas con `noindex` (login, admin, onboarding)

---

## G. Searticle

**N/A** — Webnu no usa Searticle ni blog nativo.

---

## H. Smoke test funcional

- [ ] Landing carga (es/en/fr)
- [ ] Registro de usuario nuevo
- [ ] Onboarding completo
- [ ] Carta pública accesible (`/carta/demo`)
- [ ] Panel admin superadmin accesible
- [ ] Sin errores 500 en rutas críticas
- [ ] Logs revisados (`storage/logs/laravel.log`)

---

## I. Post-lanzamiento (primeras 24–48 h)

- [ ] Monitorizar cola (jobs pendientes)
- [ ] Monitorizar webhooks Stripe
- [ ] Search Console: sin errores de rastreo críticos
- [ ] Analytics recibiendo datos (tras consentimiento)
- [ ] Alertas de leads / errores operativos

---

## Notas

_Espacio para incidencias, URLs de staging, contactos de soporte, etc._
