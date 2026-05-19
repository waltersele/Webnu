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

Las conversaciones de Cursor **no** se suben a GitHub (solo código). Ver `.gitignore` → `.cursor/conversations/`.

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
| [docs/CREDENCIALES-DEMO-LOCAL.md](docs/CREDENCIALES-DEMO-LOCAL.md) | Usuarios `demo123` para pruebas |
| [docs/HISTORIAL-CURSOR-WEBNU.md](docs/HISTORIAL-CURSOR-WEBNU.md) | Resumen del desarrollo con Cursor |
| [.cursor/README.md](.cursor/README.md) | Workspace Cursor e historial importado |

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

## Importante en despliegues

- No reemplazar `.env` ni `public/.htaccess` al subir builds.
- Configurar `GEMINI_API_KEY` o clave en Plataforma → Escaneo IA.
- `SUPER_ADMIN_EMAILS` para acceso al panel plataforma.

## Licencia

Proyecto privado — uso según acuerdo del titular del repositorio.
