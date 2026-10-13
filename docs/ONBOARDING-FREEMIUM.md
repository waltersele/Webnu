# Onboarding y planes freemium

## Resumen

Los usuarios nuevos entran con plan **`free`**, reciben **trial Pro 30 días** (`trial_tier` / `trial_days` en `config/plans.php`), completan un onboarding de **6 pasos** y pueden usar el panel **sin** suscripción Stripe, con límites de Free al acabar el trial.

Fuente de verdad: [`config/plans.php`](../config/plans.php) y `App\Services\UserPlanService`.

## Onboarding (`/admin/onboarding`)

| Paso | Contenido |
|------|-----------|
| 1 | Bienvenida + trial Pro 30 días |
| 2 | Negocio, slug y URL de la carta |
| 3 | Plantilla (Free: solo `plans.free_template_keys`) |
| 4 | Idioma base + extras / traducción (Pro+) |
| 5 | Escaneo IA o creación manual |
| 6 | Publicar carta (`company.enabled`), slug bloqueado, `onboarding_completed_at` |

**Archivos:**

- Controlador: `app/Http/Controllers/Admin/OnboardingController.php` (`MAX_STEP = 6`)
- Vistas: `resources/views/admin/onboarding/`
- Assets: `public/css/webnu-onboarding.css`, `public/js/webnu-onboarding.js`

**Flujo tras registro:** `RegisterController` / `GoogleAuthController` crean usuario (`plan = free`, `onboarding_step = 1`, `trial_ends_at` + `trial_plan_key`) y redirigen a `admin.onboarding`.

## Middleware

| Alias | Clase | Efecto |
|-------|--------|--------|
| `onboarding.complete` | `EnsureOnboardingComplete` | Redirige al onboarding si no está completado |
| `subscribed` | `EnsureSubscribed` | Permite freemium (no exige Stripe) |
| `not.suspended` | — | Bloquea cuentas suspendidas (admin/manual); **fin de trial no suspende** |

Grupo admin principal en `routes/web.php`:

```text
auth → not.suspended → subscribed → onboarding.complete → selected.company
```

## Planes (`config/plans.php`)

| Plan | Precio | Cartas | Escaneos IA | Vídeos / traducción / PDF | TVPik |
|------|--------|--------|-------------|---------------------------|-------|
| **free** | 0 € | 1 | **1** vitalicio | No | No |
| **pro** | 9,90 €/mes | 5 | 5 / mes | Sí (límites Pro) | No |
| **plus** | 19,90 €/mes | Ilimitadas* | Ilimitados* | Sí | 1 pantalla |
| **franchise** | A medida | Ilimitadas* | Ilimitados* | Sí | Sí |

\* Tope anti-abuso por hora: `config/menu_scan.php` → `scans_per_hour`.

**Aliases legacy** en `users.plan`: `plus` → `pro`, `unlimited` → `plus`.

**Trial:** 30 días en tier `pro`. Al caducar, `webnu:expire-trials` deja `plan=free` **sin suspender**; la carta publicada sigue online.

## Resolución de plan (`UserPlanService::planKey`)

1. Superadmin → `plus` (máximo tier interno)
2. Plan manual vigente (`manual_plan_key` / `manual_plan_until`)
3. Trial genérico activo → `trial_plan_key` (Pro)
4. Suscripción Stripe activa → `subscription_map`
5. Si no → `users.plan` (p. ej. `free`)

## Escaneos IA (Free)

Cada `POST /admin/menu-scan` cuenta un job. En Free el tope es **1** escaneo vitalicio (`menu_scans` + `menu_scans_period: lifetime`).

Mensaje al agotar: invitación a Pro/Plus en ajustes / billing.

## Probar onboarding de cero

1. Registro en `/register` o landing.
2. Deberías llegar a `/admin/onboarding`.
3. Para repetir en un usuario de prueba:

```sql
UPDATE users SET onboarding_completed_at = NULL, onboarding_step = 1, plan = 'free',
  trial_plan_key = 'pro', trial_ends_at = DATE_ADD(NOW(), INTERVAL 30 DAY)
WHERE email = 'tu@email.com';
```

## Carta demo en landing

`/carta/demo?tpl=lumiere|...` — solo slug `demo`, ver `PagesController@see_menu`.
