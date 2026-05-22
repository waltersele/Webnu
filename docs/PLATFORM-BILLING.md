# Plataforma Webnu — clientes y facturación Stripe

## Panel superadmin

Usuarios con acceso:

1. Email en `SUPER_ADMIN_EMAILS` del `.env` (separados por coma), **o**
2. Rol Spatie `super-admin` (asignable desde la ficha del cliente en `/admin/platform/users/{id}`).

Tras configurar el email, ejecuta:

```bash
php artisan db:seed --class=PlatformRolesSeeder
```

## Facturación automática (Stripe + Cashier)

Los cobros recurrentes los procesa **Stripe**. La app sincroniza el estado vía **webhook**.

### Variables `.env`

```env
STRIPE_KEY=pk_...
STRIPE_SECRET=sk_...
STRIPE_WEBHOOK_SECRET=whsec_...

# Pro (9,90 €/mes sin IVA)
STRIPE_PRICE_PRO_MONTHLY=price_...
STRIPE_PRICE_PRO_YEARLY=price_...

# Plus (19,90 €/mes sin IVA)
STRIPE_PRICE_PLUS_MONTHLY=price_...
STRIPE_PRICE_PLUS_YEARLY=price_...

# TVPik add-ons
STRIPE_PRICE_TVPIK_1=price_...
STRIPE_PRICE_TVPIK_PACK5=price_...

# Legacy (mapean a Pro si no defines los de arriba)
STRIPE_PRICE_MONTHLY=price_...
STRIPE_PRICE_YEARLY=price_...

WEBNU_FRANCHISE_EMAIL=hola@webnu.es
SUPER_ADMIN_EMAILS=tu@email.com
```

### Planes (`config/plans.php`)

| Tier | Precio (sin IVA) | Resumen |
|------|------------------|---------|
| `free` | 0 € | 2 cartas, 30 platos/carta, 1 scan IA, badge by Webnu |
| `pro` | 9,90 €/mes | 5 cartas, fotos, vídeos, 3 idiomas, PDF, IA ilimitada |
| `plus` | 19,90 €/mes | Todo Pro + cartas/idiomas ∞, 1 pantalla TVPik |
| `franchise` | A medida | Asignación manual en plataforma |

Add-ons TVPik: 1 pantalla 5 €/mes · pack 5 pantallas 20 €/mes (`users.tvpik_extra_screens`).

### Migración de claves legacy

Si en BD quedan `users.plan = plus` o `unlimited` (nombres antiguos):

```bash
php scripts/migrate-plan-keys.php
```

O manualmente: `plus` → `pro`, `unlimited` → `plus`.

Los alias en `plans.tier_aliases` siguen resolviendo valores antiguos en runtime.

### Panel superadmin: precios Stripe

Ruta **`/admin/platform/billing`** (menú Plataforma → Facturación):

- Crear precios en Stripe (Pro, Plus, add-ons TVPik) con un clic.
- Pegar manualmente un `price_…` existente.
- Los IDs se guardan en `platform_settings` (prioridad sobre `.env`).

### Alta con tarjeta

[`/welcome`](../resources/views/welcome.blade.php) → `process_subscription` (plan Pro/Plus, ciclo mensual/anual, add-on TVPik opcional).

### Cliente en superadmin

**`/admin/platform/users/{id}`**: suscripción Stripe (cancelar/reanudar), facturas, **plan manual** (`users.plan`) y pantallas TVPik extra.

### Acceso al panel

El middleware `subscribed` permite **Free** con límites (`UserPlanService`). Suscripciones Stripe activas se mapean en `plans.subscription_map` (p. ej. `planqrmensual` → `pro`).

Trial al registrarse: 30 días de **Pro** (`plans.trial_tier`).

## TVPik

Ver **[PLATFORM-BILLING-TVPIK.md](PLATFORM-BILLING-TVPIK.md)**.
