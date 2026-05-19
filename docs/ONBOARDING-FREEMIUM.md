# Onboarding y planes freemium

## Resumen

Los usuarios nuevos entran con plan **`free`**, completan un onboarding de **5 pasos** y pueden usar el panel sin suscripción Stripe, con límites definidos en configuración.

## Onboarding (`/admin/onboarding`)

| Paso | Contenido |
|------|-----------|
| 1 | Bienvenida y resumen del plan |
| 2 | Nombre del negocio |
| 3 | Plantilla visual (Lumière, Bistro, Nocturne, etc.) |
| 4 | Escaneo IA o creación manual de la carta |
| 5 | QR + publicar carta |

**Archivos:**

- Controlador: `app/Http/Controllers/Admin/OnboardingController.php`
- Vistas: `resources/views/admin/onboarding/`
- Assets: `public/css/webnu-onboarding.css`, `public/js/webnu-onboarding.js`

**Flujo tras registro:** `RegisterController` crea usuario (`plan = free`, `onboarding_step = 1`), empresa por defecto y redirige a `admin.onboarding`.

## Middleware

| Alias | Clase | Efecto |
|-------|--------|--------|
| `onboarding.complete` | `EnsureOnboardingComplete` | Redirige al onboarding si no está completado |
| `subscribed` | `EnsureSubscribed` | Permite freemium (ya no bloquea sin Stripe) |

Rutas del onboarding y escaneo/billing están **exentas** del middleware de onboarding para poder usar el paso 4 y mejorar plan.

Grupo admin principal en `routes/web.php`:

```text
auth → subscribed → onboarding.complete → selected.company
```

## Planes (`config/plans.php`)

| Plan | Cartas | Escaneos IA | Vídeos | TVPik |
|------|--------|-------------|--------|-------|
| **free** | 1 | 5 (total cuenta) | No | No |
| **plus** | 5 | Ilimitados* | Sí | No |
| **unlimited** | ∞ | Ilimitados* | Sí | Sí |

\* En Plus/Ilimitado aplica tope por hora anti-abuso (`config/menu_scan.php` → `scans_per_hour`).

Servicio: `App\Services\UserPlanService` — métodos `canUseMenuScan()`, `menuScansRemaining()`, `assertCanCreateCompany()`, `assertCanUseVideos()`, etc.

## Base de datos

Migración: `2026_05_20_100000_add_plan_and_onboarding_to_users_table.php`

```bash
php artisan migrate
```

- `users.plan` — default `free`
- `users.onboarding_step`
- `users.onboarding_completed_at` — usuarios ya existentes se marcan completados al migrar

## Escaneos IA (plan Gratis)

Cada `POST /admin/menu-scan` crea un `MenuScanJob` asociado al `user_id`. El límite de 5 cuenta **todos** los jobs del usuario (éxito o fallo).

UI: badge en onboarding y aviso en `resources/views/admin/menu-scan/create.blade.php`.

Mensaje al agotar: invitación a Plus en `/admin/billing`.

## Suscripciones Stripe

El plan interno se resuelve con `UserPlanService::planKey()`:

1. Superadmin → `unlimited`
2. Suscripción activa Cashier → mapeo en `config/plans.php` → `subscription_map`
3. Sin suscripción → `users.plan` (p. ej. `free`)

Ajusta `subscription_map` cuando tengas los `price_id` de Plus e Ilimitado en Stripe.

## Probar onboarding de cero

1. Registro en `/register` o formulario de la landing (`business_name` recomendado).
2. Deberías llegar a `/admin/onboarding`.
3. Para repetir en un usuario de prueba:

```sql
UPDATE users SET onboarding_completed_at = NULL, onboarding_step = 1, plan = 'free' WHERE email = 'tu@email.com';
```

4. Borrar cookies de sesión o usar ventana privada.

## Carta demo en landing

`/carta/demo?tpl=lumiere|bistro|nocturne|temporada|catalogo` — solo slug `demo`, ver `PagesController@see_menu`.
