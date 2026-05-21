# Plataforma Webnu — clientes y facturación Stripe

## Panel superadmin

Usuarios con acceso:

1. Email en `SUPER_ADMIN_EMAILS` del `.env` (separados por coma), **o**
2. Rol Spatie `super-admin` (asignable desde la ficha del cliente en `/admin/platform/users/{id}`).

Tras configurar el email, ejecuta:

```bash
php artisan db:seed --class=PlatformRolesSeeder
```

En local, con `demo@webnu.local`:

```env
SUPER_ADMIN_EMAILS=demo@webnu.local
```

Rutas:

- `/admin/platform` — dashboard (MRR, activos, impagados)
- `/admin/platform/users` — listado de clientes
- `/admin/platform/users/{id}` — detalle, facturas, acciones

## Facturación automática (Stripe + Cashier)

Los cobros recurrentes los procesa **Stripe**. La app sincroniza el estado vía **webhook**.

### Variables `.env`

```env
STRIPE_KEY=pk_...
STRIPE_SECRET=sk_...
STRIPE_WEBHOOK_SECRET=whsec_...
STRIPE_PRICE_MONTHLY=price_...
STRIPE_PRICE_YEARLY=price_...
SUPER_ADMIN_EMAILS=tu@email.com
```

### Webhook en producción

1. Stripe Dashboard → Developers → Webhooks → Add endpoint
2. URL: `https://tudominio.com/stripe/webhook`
3. Eventos recomendados (Cashier): `customer.subscription.*`, `invoice.payment_*`, `customer.updated`, `payment_method.attached`
4. Copia el **Signing secret** a `STRIPE_WEBHOOK_SECRET`

### Webhook en local (Stripe CLI)

```bash
stripe listen --forward-to http://127.0.0.1:8000/stripe/webhook
```

Usa el `whsec_...` que muestra el CLI en tu `.env` local.

### Portal de facturación (cliente)

Los clientes sin suscripción activa son redirigidos a `/admin/billing`. Desde ahí pueden abrir el **portal de Stripe** para actualizar tarjeta o cambiar plan.

### Planes

Configurados en [`config/billing.php`](../config/billing.php):

- Mensual: `planqrmensual` — 10 €/mes
- Anual: `planqranual` — 100 €/año

Alta de nuevos clientes: página [`/welcome`](../resources/views/welcome.blade.php) → `process_subscription`.

## Acceso al panel

El middleware `subscribed` permite el **plan Gratis** con límites (`UserPlanService`). Los clientes con suscripción Stripe activa (`planqrmensual` / `planqranual`) obtienen plan **Plus** vía `subscription_map` en [`config/plans.php`](../config/plans.php).

Migración a producción: prioridad cartas/QR en [MIGRACION-PRODUCCION.md](MIGRACION-PRODUCCION.md). Si el cobro es manual (sin Stripe activo), asignad `users.plan` en BD hasta reactivar suscripciones.

## TVPik (facturación en Webnu)

Los servicios de **pantallas TV / TVPik** se facturan y limitan desde Webnu. TVPik no tiene Stripe propio: consume `GET /api/signage/account`.

Documentación del contrato: **[PLATFORM-BILLING-TVPIK.md](PLATFORM-BILLING-TVPIK.md)**.

