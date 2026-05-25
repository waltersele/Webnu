# Webnu

Carta digital para restaurantes: menú público por QR, panel de administración, escaneo IA de cartas en papel y planes freemium.

**Repositorio:** https://github.com/waltersele/Webnu

## Abrir en Cursor / VS Code

Usa el workspace incluido en el repo (recomendado):

```text
Webnu.code-workspace
```

- Oculta `vendor/` y `node_modules/` en el explorador.
- Extensiones sugeridas: Intelephense, Laravel Blade.
- Contexto del agente: [.cursor/README.md](.cursor/README.md) y [docs/HISTORIAL-CURSOR-WEBNU.md](docs/HISTORIAL-CURSOR-WEBNU.md).

Las conversaciones de Cursor se versionan en **`.cursor/conversations/`** para conservar contexto. Antes de cada push ejecuta `.\scripts\sync-cursor-conversations.ps1`.

## Requisitos

- PHP 7.4+ (proyecto Laravel 7) o PHP 8.x en local
- Composer, Node.js (assets con Laravel Mix)
- SQLite o MySQL para la base de datos
- Opcional: Tesseract (OCR fallback), API Gemini (escaneo IA)

## Arranque rápido (local)

```powershell
cd c:\webProject\webnu\Webnu
.\run-local.ps1
```

O manualmente:

```bash
composer install
cp .env.example .env   # si no existe .env
php artisan key:generate
php artisan migrate
php scripts/seed-platform-demo.php
npm install
npm run dev
php artisan serve
```

- **Web:** http://127.0.0.1:8000/
- **Registro / landing:** `/` (Blade + Tailwind CDN)
- **Admin:** http://127.0.0.1:8000/admin
- **Onboarding (usuarios nuevos):** http://127.0.0.1:8000/admin/onboarding
- **Carta demo:** http://127.0.0.1:8000/carta/demo?tpl=lumiere

Credenciales de prueba: [docs/CREDENCIALES-DEMO-LOCAL.md](docs/CREDENCIALES-DEMO-LOCAL.md)

## Funcionalidades principales

| Área | Descripción |
|------|-------------|
| **Landing** | Página principal en `/` (`landing-preview.blade.php`), precios freemium, registro sin Stripe en el hero |
| **Onboarding** | Wizard de 5 pasos tras el registro (nombre, plantilla, escaneo IA o manual, QR) |
| **Planes** | Gratis / Plus 9,90 € / Ilimitado 29,90 € — ver [docs/ONBOARDING-FREEMIUM.md](docs/ONBOARDING-FREEMIUM.md) |
| **Carta pública** | `https://tudominio/carta/{slug}` con plantillas (Lumière, Bistro, etc.) |
| **Estudio de negocio** | Editor visual de plantilla, colores y preview en iframe |
| **Escaneo IA** | Importar carta desde foto/PDF (Gemini + OCR) — [docs/MENU-SCAN.md](docs/MENU-SCAN.md) |
| **Plataforma** | Superadmin: clientes, Gemini, facturación — [docs/PLATFORM-BILLING.md](docs/PLATFORM-BILLING.md) |

## Plan Gratis (freemium)

- 1 carta (negocio)
- **5 escaneos IA** por cuenta (vitalicios, contados en `menu_scan_jobs`)
- Sin vídeos en platos
- Panel admin accesible sin suscripción Stripe

Límites en `config/plans.php` y `App\Services\UserPlanService`.

## Documentación

| Archivo | Contenido |
|---------|-----------|
| [docs/ONBOARDING-FREEMIUM.md](docs/ONBOARDING-FREEMIUM.md) | Onboarding, middleware, migración `plan` |
| [docs/MENU-SCAN.md](docs/MENU-SCAN.md) | Gemini, Tesseract, flujo de importación |
| [docs/PLATFORM-BILLING.md](docs/PLATFORM-BILLING.md) | Stripe, webhooks, panel plataforma |
| [docs/deploy.md](docs/deploy.md) | **Despliegue diario a producción** (script, rollback, troubleshooting) |
| [docs/deploy-migrations.md](docs/deploy-migrations.md) | Despliegue con foco en migraciones de BD |
| [docs/MIGRACION-PRODUCCION.md](docs/MIGRACION-PRODUCCION.md) | Despliegue prod, backup, QR/slugs |
| [docs/TVPIK-INTEGRATION.md](docs/TVPIK-INTEGRATION.md) | Publicar carta en TV desde Webnu |
| [docs/PLATFORM-BILLING-TVPIK.md](docs/PLATFORM-BILLING-TVPIK.md) | Webnu cobra; TVPik consulta permisos (API) |
| [docs/CREDENCIALES-DEMO-LOCAL.md](docs/CREDENCIALES-DEMO-LOCAL.md) | Usuarios `demo123` para pruebas |
| [docs/HISTORIAL-CURSOR-WEBNU.md](docs/HISTORIAL-CURSOR-WEBNU.md) | Resumen del desarrollo con Cursor |
| [.cursor/README.md](.cursor/README.md) | Workspace Cursor |
| [.cursor/conversations/](.cursor/conversations/) | Transcripts JSONL del chat (contexto versionado) |

## Migraciones recientes

Tras `git pull`, ejecuta:

```bash
php artisan migrate
```

Incluye campos `users.plan`, `onboarding_step`, `onboarding_completed_at` (usuarios existentes quedan con onboarding completado).

## Assets front

- **Landing producción:** Blade en `resources/views/landing-preview.blade.php` + `public/js/landing-preview.js`
- **Landing React (legacy):** `resources/js/landing/` — compilar con `npm run dev` / `npm run production` si se retoma
- **Onboarding:** `public/css/webnu-onboarding.css`, `public/js/webnu-onboarding.js`

## Despliegue en producción

Flujo del día a día (resumen). Guía completa en **[docs/deploy.md](docs/deploy.md)**.

```bash
# En tu equipo
git push origin main

# En el servidor (cPanel + SSH)
ssh wwwwebnu@webnu.es
cd /home/wwwwebnu/public_html/webnu.es
./scripts/deploy.sh
```

El script `scripts/deploy.sh` hace `git fetch`, calcula los ficheros cambiados desde el último deploy, hace `mysqldump` automático, `rsync` quirúrgico, `composer dump-autoload --ignore-platform-reqs`, `migrate --force`, ejecuta los seeders production-safe nuevos y refresca cachés. Soporta `--dry-run`, `--rollback`, `--skip-migrate`, `--skip-backup`, `--no-down` y `--full-rsync`.

**Importante**:
- No reemplazar `.env`, `public/.htaccess`, `public/img/`, `vendor/`, `composer.{json,lock}` ni `storage/` al subir builds (el script los excluye).
- Configurar `GEMINI_API_KEY` o clave en Plataforma → Escaneo IA.
- `SUPER_ADMIN_EMAILS` para acceso al panel plataforma.
- Mientras el server siga en PHP 7.4, **no ejecutes `composer install`** (el `composer.json` apunta a PHP ^8.1).

## Licencia

Proyecto privado — uso según acuerdo del titular del repositorio.
