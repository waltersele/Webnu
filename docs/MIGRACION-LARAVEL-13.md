# Migración de Webnu a Laravel 13 y adopción de colorful/saas-core

Documento de trabajo para Claude Code. Repo: `waltersele/Webnu` (carpeta local buena: `C:\webproject\Webnu\Webnu`; **no** usar `C:\webproject\Webnu`, es el árbol antiguo con Laravel 7).

## Prompt de arranque (pégalo en Claude Code)

> Lee `docs/MIGRACION-LARAVEL-13.md` y ejecuta la Fase 1, un salto de versión por PR, en orden. Para en cada punto marcado "PARAR Y PREGUNTAR". No toques producción ni hagas deploy.

## Estado de partida (26/11/2026)

- Laravel 10, PHP ^8.3 (producción: PHP 8.3 en cPanel, CLI `/opt/alt/php83`), Cashier 15, Socialite, laravel/ui 4, spatie/laravel-permission 6, tecnickcom/tcpdf.
- Estructura clásica: `app/Http/Kernel.php`, `app/Console/Kernel.php`, 5 providers en `app/Providers`. **No migrar al skeleton slim de Laravel 11+** en esta fase; mantener la estructura actual mientras Laravel 13 lo permita.
- Assets con Laravel Mix (`webpack.mix.js`). 63 migraciones. Tests en `tests/Feature` y `tests/Unit` (PHPUnit, sqlite en memoria).
- `main` es lo que se despliega en producción. Trabajar siempre en ramas y PRs.
- Requiere la extensión PHP `bcmath` (la exige Cashier/moneyphp). Comprobar con `php -m | findstr bcmath`; si falta, activarla en `php.ini`.

## Baseline de tests (medido en Linux/sqlite sobre Laravel 10)

`php artisan test` → **140 OK / 5 KO** antes de tocar nada. Los 5 KO ya existían:

- `ConversionFunnelTest`: "company public qr url has utm medium", "login layout includes analytics beacon", "onboarding step records funnel event", "register page has cta attribute".
- `PlanLocaleLimitTest`: "idiomas permite cambiar idioma base con carta con contenido" (`sections.enabled` NOT NULL).

Primer paso: repetir el baseline en tu entorno Windows y anotar el resultado. **Regla: ningún salto puede empeorar el baseline.**

## Fase 1: subir a Laravel 13 (un PR por salto)

Ramas: `l13/paso-1-laravel-11`, `l13/paso-2-laravel-12`, `l13/paso-3-laravel-13`, cada una desde `main` actualizado (o desde la rama anterior ya mergeada).

Comandos base (PowerShell):

```powershell
cd C:\webproject\Webnu\Webnu
git checkout main
git pull origin main
git checkout -b l13/paso-1-laravel-11
composer install
php artisan test
```

### Paso 1: 10 → 11

1. Dependencias objetivo (verificar con `composer why-not` y la guía oficial antes de fijar versiones): `laravel/framework ^11`, `nunomaduro/collision ^8`, `phpunit/phpunit ^10.5` o superior compatible, `spatie/laravel-ignition ^2.4`, `laravel/tinker ^2.9`, `laravel/ui ^4.5`; comprobar Cashier, Socialite y spatie/laravel-permission.
2. Seguir la guía oficial de actualización 10.x → 11.x. Puntos a revisar en este proyecto: Carbon 3, migraciones con `->change()` (hay que redefinir todos los atributos), rate limiting por segundo, rehash de contraseñas, casts.
3. `php artisan test` debe igualar el baseline. `php artisan webnu:deploy-check` sin errores críticos.
4. PR con: salida de tests (OK/KO vs baseline), lista de cambios de `composer.json`, y cualquier cambio de código necesario.

### Paso 2: 11 → 12

Mismo método con la guía 11.x → 12.x. Comprobar versiones compatibles de Cashier, Socialite, spatie/laravel-permission y PHPUnit.

### Paso 3: 12 → 13

Mismo método con la guía 12.x → 13.x. No asumir cambios de memoria: leer la guía oficial de Laravel 13. Comprobar compatibilidad de Cashier 15+ con Laravel 13.

**PARAR Y PREGUNTAR** si algún paquete (sobre todo Cashier, laravel/ui, tcpdf) no tiene versión compatible con el salto, o si hay que cambiar su API.

### Después de la Fase 1

- PR aparte para los 5 tests que ya fallaban (no mezclar con los saltos).
- Actualizar `README.md` ("Laravel 10" → "Laravel 13") y `docs/deploy.md` (stack actual).

### Despliegue de la Fase 1 (lo hace Walter, no Claude Code)

Un deploy de versión mayor cambia `vendor/`, así que:

1. Backup previo (`scripts/backup-production.ps1` o el que genera `deploy.sh`).
2. En el servidor: `./scripts/deploy.sh --dry-run` y después `./scripts/deploy.sh --with-vendor`.
3. Confirmar en cPanel (MultiPHP Manager) que `webnu.es` usa **PHP 8.3** (si el handler web queda en otra versión, la web da HTTP 500).
4. Comprobar `webnu.es`, login, carta pública, registro y Stripe (modo test) antes de dar el deploy por bueno. Plan de vuelta atrás: `./scripts/deploy.sh --rollback` (no restaura la BD).

## Fase 2: adoptar `waltersele/colorboilerplate` (colorful/saas-core)

Repo privado: `waltersele/colorboilerplate`. Paquete `colorful/saas-core`, Laravel 13, PHP ^8.3, Filament v5. Guía oficial del paquete: `docs/ADOPTION.md` del repo; reglas de arquitectura: `AGENTS.md` y `docs/ARCHITECTURE.md`.

Reglas del core que hay que respetar en Webnu:

- El core no contiene dominio vertical (nada de cartas, alérgenos, TVPik…). Eso se queda en Webnu.
- No se edita el core desde Webnu: se extiende por eventos y contratos.
- Tablas del core con prefijo `saas_`. Módulos activables en `config/saas.php`.
- No añadir dependencias sin aprobación de Walter.

Orden propuesto, un PR por paso (empezar solo cuando la Fase 1 esté mergeada, desplegada y estable):

1. Instalar el paquete: repositorio VCS privado + token de GitHub, `composer require colorful/saas-core`, `php artisan saas:install`, `php artisan saas:doctor`.
2. `User` con los contratos `HasSaasAccount` y `Billable` y el trait `HasRoles`; `config/saas.php` con `user_model`.
3. Módulos `admin` y `health`; 2FA de superadmin; panel Filament con `SaasPanelPlugin`.
4. Blog y SEO: hoy Webnu tiene `BlogPost` propio y recibe contenido de Sonartop por su conector. El core ofrece `POST /api/v1/blog/connector` y `saas:blog:import`. Decidir si se migra o se mantiene el actual.
5. Billing: ver decisión pendiente.

### Decisión pendiente (PARAR Y PREGUNTAR antes del paso 5)

Webnu usa Cashier 15 con un modelo `Subscription` propio (`app/Subscription.php`, `app/Listeners/StripeWebhookSubscriptionListener.php`, `config/plans.php`, `app/Services/Billing/TvpikScreenPricing.php`). El core usa `stripe/stripe-php` directo, sin Cashier, con tablas `saas_`. Decisión tomada con Walter: **resolverlo en la Fase 2**, no antes. Opciones: (a) migrar suscripciones existentes a `saas_subscriptions` y quitar Cashier, (b) mantener Cashier y no usar el billing del core. Antes de tocar nada, presentar a Walter un inventario de datos reales y un plan de migración reversible.

## Reglas de trabajo

- Un PR = un salto de versión o un paso de la Fase 2. PRs pequeños y reversibles.
- La descripción del PR incluye: qué cubre, salida de `php artisan test` frente al baseline, y riesgos para producción.
- Antes de cada `git push` ejecutar `.\scripts\sync-cursor-conversations.ps1` si se usa Cursor (lo pide el README).
- Antes de commitear cualquier `.js`: `node --check <archivo>`.
- Nunca copiar el `.env` del árbol antiguo (`C:\webproject\Webnu`): tiene un BOM y falla.
- Si falta información, preguntar en el PR en lugar de suponer.
