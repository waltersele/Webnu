# Continuar — Webnu (oficina)

Última actualización: 2026-05-27

## Hecho en esta sesión

- URLs públicas: formato **simple** (1ª carta) vs **nested** (cartas adicionales / legacy).
- Registro, Google OAuth y onboarding paso 2: negocio + carta + slug + preview QR.
- Tabla `public_slug_redirects` + 301, bloqueo de slug tras publicar.
- `PublicPathRegistry`, validación de paths únicos, menús en `/carta/.../menu/...`.
- Comando `php artisan webnu:repair-public-url {email}`.
- JS `public/js/webnu-public-url-preview.js`.
- Tests: `tests/Feature/PublicUrlSignupTest.php`.
- Onboarding / idiomas / planes (Pro = 3 idiomas en carta) de sesiones anteriores en el mismo commit.

## Al retomar mañana

### 1. Despliegue / BD (primero)

```bash
php artisan migrate
```

En producción, tras push: el usuario ejecuta SSH deploy (no automatizado desde el agente).

### 2. Problema pendiente: **URL + idioma**

El idioma de la carta pública va por **query string** (`?lang=en`), no por path:

- Ejemplo hoy: `webnu.es/carta/la-brasa?lang=en`
- La URL canónica del QR suele ser **sin** `?lang=` (idioma por defecto o `Accept-Language`).

**Riesgos / decisiones a tomar:**

| Tema | Situación actual | Qué valorar |
|------|------------------|-------------|
| SEO | Misma path, varios idiomas sin `rel="canonical"` ni `hreflang` por carta | Añadir canonical + `hreflang` en `themes/partials/head.blade.php` (como en landing) |
| QR | QR apunta a URL sin idioma; visitante puede ver otro idioma por navegador | ¿Fijar idioma en QR (`?lang=es`) o mantener detección automática? |
| URLs simple vs nested | `MenuLocaleService::menuUrl()` concatena `?lang=` sobre `publicUrl()` | Comprobar switcher y enlaces en hub/menús con ambos formatos |
| Duplicados | `/carta/slug` vs `/carta/owner/slug` (legacy 301) + `?lang=` | Canonical debe apuntar a una sola URL por idioma o una canónica + alternates |
| Admin preview | `languages.blade.php` usa `{{ $publicUrl }}?lang=en` | Revisar que `publicUrl()` sea la canónica correcta tras el cambio |

**Propuestas (no implementadas):**

- A) Dejar `?lang=` y mejorar SEO con `<link rel="canonical">` + `hreflang` por locale habilitado.
- B) Path por idioma: `/carta/la-brasa/en/...` (cambio mayor de rutas).
- C) Subdominio o prefijo global `/en/carta/...` (solo si hay producto multi-mercado).

### 3. SEO URLs (sesión anterior, pendiente)

- Añadir `rel="canonical"` en carta, hub y menú.
- Test `PublicUrlExamplesTest` (escenarios ficticios 200/301) — borrador no commiteado como test aparte.
- Auditoría opcional: `webnu:audit-public-urls`.

### 4. Cuentas a reparar

```bash
php artisan webnu:repair-public-url "email@ejemplo.com" \
  --business-slug=mi-negocio \
  --company-slug=mi-carta \
  --reset-onboarding
```

### 5. Verificación manual rápida

- [ ] Registro Google → paso 2 URL → publicar → QR
- [ ] Registro email con preview de URL
- [ ] Carta legacy sigue en `/carta/{owner}/{carta}`
- [ ] `GET /carta/vieja` → 301 si hay redirect en BD
- [ ] Cambiar idioma en carta pública (simple y nested) — enlaces del switcher
- [ ] Segunda carta: primera URL simple sin cambiar

### 6. Archivos clave

- Rutas: `routes/web.php`
- URL empresa: `app/Company.php`, `app/Services/PublicPathRegistry.php`
- Idioma: `app/Services/MenuLocaleService.php`, `resources/views/themes/partials/language-switcher.blade.php`
- Onboarding: `app/Http/Controllers/Admin/OnboardingController.php`, `resources/views/admin/onboarding/show.blade.php`

## Notas

- Negocios **existentes** sin `public_url_format = simple` no cambian de URL al desplegar.
- No incluir en git: `.env`, `php-local.ini`, uploads en `public/img/productos/` de prueba.
