# Credenciales de prueba (local)

Genera o actualiza estos datos con:

```bash
php scripts/seed-platform-demo.php
```

(o al arrancar con `.\run-local.ps1`, que lo ejecuta automáticamente)

**Contraseña de todos los usuarios:** `demo123`

### Migraciones del módulo comercial (obligatorio la primera vez)

Si `/admin/platform/comercial` falla con `no such table: sales_handoffs`, ejecuta en la **terminal de Laragon** (misma PHP que el servidor):

```bash
cd c:\webproject\Webnu\Webnu
php artisan migrate
```

Alternativa sin artisan: `php scripts/run-sales-migrations.php` (usa credenciales de tu `.env`).

---

## Superadmin (panel plataforma)

| Campo | Valor |
|--------|--------|
| Email | `demo@webnu.local` |
| Contraseña | `demo123` |
| Panel | http://127.0.0.1:8000/admin |
| Plataforma | http://127.0.0.1:8000/admin/platform |
| Listado clientes | http://127.0.0.1:8000/admin/platform/users |

En el menú lateral verás **Plataforma → Dashboard**, **Clientes** y **Comercial**.

---

## Portal comercial (visitas en calle)

| Campo | Valor |
|--------|--------|
| Email | `comercial@webnu.local` |
| Contraseña | `demo123` |
| Login | http://127.0.0.1:8000/comercial/login |
| Gestión (superadmin) | http://127.0.0.1:8000/admin/platform/comercial |

El usuario comercial se crea al ejecutar `php scripts/seed-platform-demo.php` (requiere migraciones aplicadas), o desde **Plataforma → Comercial → pestaña Comerciales → Nuevo comercial**.

Asegúrate de tener en `.env` (o lo define `run-local.ps1`):

```env
SUPER_ADMIN_EMAILS=demo@webnu.local
```

---

## Clientes de ejemplo (para probar estados)

| Email | Qué simula | Acceso a `/admin` |
|--------|------------|-------------------|
| `maria@webnu.local` | Suscripción activa mensual, 1 negocio | Sí — panel normal |
| `jose@webnu.local` | Plan anual activo, 2 negocios | Sí |
| `ana@webnu.local` | Periodo de prueba (14 días) | Sí |
| `luis@webnu.local` | Impago (`past_due`) | No — va a **Suscripción** |
| `carmen@webnu.local` | Cancelación al fin de periodo | Sí (hasta la fecha de fin) |
| `pablo@webnu.local` | Sin suscripción | No — solo `/admin/billing` |

Cartas públicas de ejemplo:

- http://127.0.0.1:8000/carta/demo
- http://127.0.0.1:8000/carta/casa-maria
- http://127.0.0.1:8000/carta/taberna-jose

---

## Qué probar

1. **Superadmin:** entra con `demo@webnu.local` → Plataforma → Clientes → abre María, Luis, Pablo.
2. **Bloqueo:** entra con `pablo@webnu.local` → debes ver solo la página de suscripción.
3. **Impago:** entra con `luis@webnu.local` → mismo comportamiento (billing).
4. **Cliente OK:** entra con `maria@webnu.local` → Mi carta, negocios, etc.

Los datos de suscripción son **simulados en base de datos** (no Stripe real). El botón “Gestionar pago en Stripe” solo funciona si configuras claves Stripe reales y un `stripe_id` válido en Stripe.

Para cobros reales y webhooks, sigue [PLATFORM-BILLING.md](PLATFORM-BILLING.md).
