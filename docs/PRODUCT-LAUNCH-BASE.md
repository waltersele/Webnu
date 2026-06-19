# Base de lanzamiento de producto — Webnu

Plantilla operativa para **Webnu** (Laravel B2B: carta digital, admin de plataforma e integraciones). Derivada de la guía Shoow, adaptada a las rutas, integraciones y despliegue reales de este proyecto.

Usar junto con [PRODUCT-LAUNCH-CHECKLIST.md](PRODUCT-LAUNCH-CHECKLIST.md) en cada release o go-live.

---

## 1. Filosofía de configuración

Toda integración sigue **el mismo patrón en tres capas**:

| Capa | Dónde | Para qué |
|------|--------|----------|
| **Bootstrap** | `.env` / `.env.example` | Desarrollo local, CI, primer despliegue |
| **Runtime** | Tabla `platform_settings` + `PlatformSettingsService` | Producción: cambiar credenciales sin redeploy |
| **UI** | Admin → Plataforma → Configuración (`platform.access`) | Operación: campos enmascarados, tests de conexión |

**Prioridad de lectura:** Admin (BD) → `.env` → default en `config/`.

**Secretos:** cifrados en BD. En el formulario: campo vacío = no cambiar.

**Comandos de salud:** `php artisan webnu:deploy-check` + tests en admin (Gemini, Stripe, mail).

---

## 2. Roles y superficie de administración

### Roles mínimos

| Rol | Acceso |
|-----|--------|
| `platform.access` (superadmin) | Integraciones, clientes, facturación plataforma, métricas |
| `business_owner` | Panel de su negocio, sin credenciales de plataforma |

Superadmins se definen en `SUPER_ADMIN_EMAILS` (`.env`) o vía permisos Spatie.

### Rutas admin principales

```
/admin/dashboard
/admin/platform/settings     ← integraciones, marca, medición
/admin/platform/billing      ← precios Stripe
/admin/platform/users        ← clientes
/admin/onboarding
/admin/settings              ← perfil del tenant
```

### Zonas que no deben indexarse

Middleware `noindex` + header `X-Robots-Tag: noindex, nofollow` en:

- `/login`, `/register`, `/auth/*`
- `/admin/*`, `/onboarding`, `/settings`, `/billing`
- `/comercial/*`, `/integrations/*`, `/pre-alta/*`, `/activar/*`

`public/robots.txt` como segunda línea de defensa (`Disallow` de rutas privadas).

**Indexables:** `/` (landing), `/carta/*` (cartas de clientes), `/tv/*` (reproductor).

---

## 3. Catálogo de integraciones Webnu

### 3.1 Pagos — Stripe (Cashier)

| Elemento | Webnu |
|----------|-------|
| Keys admin | `stripe_key`, `stripe_secret`, `stripe_webhook_secret` |
| Fallback `.env` | `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET` |
| Webhook | `POST /stripe/webhook` (Cashier, excluido de CSRF) |
| Precios | Admin → Facturación plataforma + `platform_settings` |
| Verificación | Botón «Probar Stripe» en admin + `webnu:deploy-check` |

### 3.2 Email — SMTP

| Elemento | Detalle |
|----------|---------|
| Keys admin | `mail_host`, `mail_port`, `mail_username`, `mail_password` (cifrada), etc. |
| Fallback | Variables `MAIL_*` en `.env` |
| Patrón | `PlatformMailConfigurator` aplica la config al boot |

### 3.3 OAuth — Google (login social)

| Elemento | Detalle |
|----------|---------|
| Keys admin | `google_client_id`, `google_client_secret` |
| `.env` | `GOOGLE_REDIRECT_URI` = `{APP_URL}/auth/google/callback` |
| Rutas | `GET /auth/google`, `GET /auth/google/callback` |

### 3.4 IA — Gemini (escaneo de carta)

| Elemento | Detalle |
|----------|---------|
| Key admin | `gemini_api_key` (cifrada), `gemini_model` |
| Fallback | `GEMINI_API_KEY` en `.env` |
| Uso | `GeminiMenuScanProvider`, onboarding paso captura |
| Verificación | Botón «Probar Gemini» en admin |

> A diferencia de Shoow, Gemini **vive en Webnu** (no en Searticle).

### 3.5 TVPik / pantallas digitales

| Elemento | Detalle |
|----------|---------|
| Keys admin | `tvpik_api_url`, `tvpik_web_url`, `tvpik_app_key`, `digital_signage_app_key` |
| Documentación | [TVPIK-INTEGRATION.md](TVPIK-INTEGRATION.md), [TV-PLAYER-MODE.md](TV-PLAYER-MODE.md) |
| Reproductor sin TVPik | `/tv/{company}?player=1` funciona sin conexión hub |

### 3.6 Pre-Alta (captura externa)

| Elemento | Detalle |
|----------|---------|
| Key admin | `pre_alta_ingest_key` (cifrada) |
| Documentación | [PRE-ALTA.md](PRE-ALTA.md) |

### 3.7 Medición y analítica (landing pública)

**Servicio:** `MeasurementSettingsService`.

| Herramienta | Key admin | Notas |
|-------------|-----------|-------|
| Google Search Console | `google_site_verification` | Meta tag; no requiere cookies |
| Google Analytics 4 | `gtag_measurement_id` (`G-XXXX`) | Tras consentimiento |
| Google Tag Manager | `gtm_container_id` (`GTM-XXXX`) | Opcional |
| Microsoft Clarity | `clarity_project_id` | Tras consentimiento |

**Interruptores:** `measurement_enabled`, `cookie_banner_enabled`.

**Frontend:** `resources/views/partials/measurement-head.blade.php` + `public/js/webnu-measurement.js`.

> Las cartas públicas de clientes (`/carta/*`) **no** cargan analytics de plataforma. Analytics por tenant queda fuera de alcance v1.

### 3.8 N/A en Webnu

- **Searticle / blog / DeepL:** no aplica (Webnu no tiene blog nativo ni conector Searticle).
- **Twilio SMS:** no integrado.
- **Resend:** no obligatorio; se usa SMTP genérico.

---

## 4. Identidad visual y assets

Panel **Admin → Plataforma → Configuración → Marca**:

| Maestro | Uso |
|---------|-----|
| Logo | Nav, panel, emails |
| Isotipo | Favicon, PWA |
| Favicon | Pestaña navegador |
| Open Graph | Compartir landing en redes (1200×630) |

Rutas públicas:

```
public/img/brand/          ← uploads admin (logo, isotipo, favicon, og)
public/img/pwa/            ← icon-192.png, icon-512.png, icon-512-maskable.png
public/manifest.webmanifest
public/manifest-admin.webmanifest
```

Regenerar iconos PWA (CLI): `scripts/gen-pwa-icons.ps1` (Windows) o subir isotipo en admin.

Color de marca: `#004ac6` — alinear `theme_color` del manifest.

---

## 5. SEO y Search Console

| Ítem | Qué comprobar |
|------|----------------|
| Verificación GSC | Meta `google-site-verification` desde admin |
| Sitemap | `https://webnu.es/sitemap.xml` → 200 |
| robots.txt | `Disallow` rutas privadas + `Sitemap:` |
| Canonical + OG | Landing (`landing/partials/head.blade.php`) |
| hreflang | Landing multilingüe (`?lang=es|en|fr`) |
| JSON-LD | WebSite + Organization + SoftwareApplication en landing |
| noindex | Admin, auth, onboarding, comercial |
| HTTPS | `APP_URL=https://`, cookies secure en producción |

**Sitemap:** solo páginas de plataforma (`/`, `/register`, legales). No incluir `/carta/*`.

---

## 6. Seguridad de conexiones

| Tipo | Mecanismo |
|------|-----------|
| Secretos en BD | Cifrado vía modelo `PlatformSetting` |
| Webhooks Stripe | Firma Cashier + raw body |
| Pre-Alta ingest | API key + middleware `pre_alta.ingest` |
| CSRF | Excepciones solo en webhooks |
| Admin integraciones | Solo `platform.access` |
| Rotación APP_KEY | Reintroducir secretos en admin |

---

## 7. Infraestructura de despliegue

Ver [deploy.md](deploy.md) para el flujo completo.

### Post-deploy (cada release)

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
php artisan webnu:deploy-check
```

### Cron obligatorio

```cron
* * * * * php artisan schedule:run
```

Cola: `QUEUE_CONNECTION=database` + worker `queue:work`.

---

## 8. Añadir una integración nueva

```
□ Key en PlatformSetting + PlatformSettingsService
□ Entrada en config/services.php → env()
□ Variable en .env.example
□ Validación en PlatformSettingsController@update
□ Sección en admin/platform/settings.blade.php
□ Configurator *::apply() al boot si aplica
□ Botón o comando artisan *:test
□ Línea en webnu:deploy-check
□ Documentar en docs/
```
