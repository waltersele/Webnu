# Medición unificada (Webnu)

Loader compartido: `public/js/measurement.js` (idéntico al de Shoow). Consent Mode v2 + Plausible vía proxy same-origin + marketing solo con consentimiento de marketing.

## Tres capas

| Capa | Qué | Consentimiento |
|------|-----|----------------|
| **1 — Exempt** | Plausible (script + API vía `/stats/...`) | No requiere banner; sin cookies de identificación |
| **2 — Google Consent Mode** | gtag o GTM; analítica **obligatoria** tras decisión del banner | `loadGoogleBeforeConsent: true` carga el script en denied hasta interactuar; tras cualquier decisión del banner, `analytics_storage` queda granted |
| **3 — Marketing** | Meta Pixel, LinkedIn Insight | Única categoría rechazable; solo si `marketing === true` |

**Nota GTM / Ads:** si en el futuro se usa un contenedor GTM con etiquetas de Google Ads, deben exigir `ad_storage` granted. No disparar Ads solo con consentimiento de analítica.

## Schema `publicConfig`

Emitido por `MeasurementSettingsService::publicConfig()` e inyectado en `#webnu-measurement-config`:

```json
{
  "enabled": true,
  "brand": "webnu",
  "cookieBanner": true,
  "loadGoogleBeforeConsent": true,
  "exempt": {
    "plausibleDomain": "webnu.es",
    "plausibleScriptUrl": "/stats/js/script.js",
    "plausibleApiUrl": "/stats/api/event"
  },
  "consented": {
    "gtmId": null,
    "gtagId": "G-XXXXXXXX",
    "clarityId": null,
    "metaPixelId": null,
    "linkedinPartnerId": null
  },
  "pendingEvent": null
}
```

- `enabled`: medición plataforma on **o** Plausible con upstream configurado.
- Si hay GTM, `gtagId` se anula.
- El JS normaliza schemas legacy (`tools.gtag.id`, `tools.gtmId`, etc.).

## Consentimiento persistido

- Cookie first-party `{brand}_cookie_consent` (`SameSite=Lax`, `Secure` en HTTPS, ~13 meses) + espejo en `localStorage`.
- Payload: `{ v: 2, necessary, analytics, marketing, updatedAt }`. `analytics` siempre se persiste como `true`.
- Migración v1 → v2: si el consentimiento antiguo tenía `analytics: true`, se migra en silencio (marketing se conserva). Si tenía `analytics: false` (o legacy `'rejected'`), se invalida y **vuelve a salir el banner**.
- Legacy `'accepted'` → analytics true, marketing false (migrado a v2).

## Banner

Botones: **Rechazar opcionales** | **Guardar selección** (solo si hay marketing) | **Aceptar todas**. Necesarias y Analítica van checked + disabled. Marketing desmarcado por defecto. Footer y legal: enlace `data-manage-cookies`.

## Cómo añadir una herramienta

1. **Analítica (capa 2):** campo en admin + `consented.*`; tras cualquier decisión del banner queda granted (obligatoria).
2. **Marketing (capa 3):** campo en admin + cargar **solo** si `consent.marketing === true` (rechazable).
3. **Exempt (capa 1):** solo herramientas sin cookies de identificación; URLs en `exempt.*`.

## Proxy Plausible

- `GET /stats/js/script.js` → `{PLAUSIBLE_UPSTREAM_URL}/js/script.js`
- `POST|GET /stats/api/event` → `{upstream}/api/event` (CSRF except)
- Si upstream vacío → 404; el loader no inserta Capa 1.

Variables (`.env` / Admin):

- `PLAUSIBLE_DOMAIN` (p. ej. `webnu.es`)
- `PLAUSIBLE_UPSTREAM_URL` (default `https://plausible.io`; si hay dominio y upstream vacío, se usa ese default para no dejar Capa 1 muerta)
- `PLAUSIBLE_SCRIPT_URL`, `PLAUSIBLE_API_URL` (defaults `/stats/...`)
- `MEASUREMENT_LOAD_GOOGLE_BEFORE_CONSENT=true`

## Admin

Tarjeta **Medición y Search Console** incluye `measurement_section=1`. Solo si ese campo llega en el POST se actualizan keys de medición.

## Checklist DevTools (aceptación)

Con `PLAUSIBLE_UPSTREAM_URL` configurado:

1. Banner sin tocar: hits a `/stats/api/event`; cero cookies `_ga*` hasta decidir.
2. Con `loadGoogleBeforeConsent: true`: gtag/GTM carga; `/g/collect` denegado (`gcs=G100`) hasta interactuar; sin `_ga`.
3. Aceptar todas: analytics + ads granted; cookies `_ga`.
4. Rechazar opcionales: analytics granted, ads denied; sin `doubleclick` / `ccm/collect`.
5. Consentimiento v1 con analytics false (o legacy rejected): vuelve a salir el banner v2.
6. Recarga conserva decisión; “Gestionar cookies” reabre.

## Sync con Shoow

`public/js/measurement.js` debe ser byte-igual en ambos repos. Tras editarlo en uno, copiarlo al otro.

## Archivos clave

- `public/js/measurement.js` — loader
- `public/css/measurement-consent.css`
- `public/js/webnu-measurement.js` — stub deprecado
- `resources/views/partials/measurement-head.blade.php`
- `config/measurement.php`
- `app/Services/Platform/MeasurementSettingsService.php`
- `app/Http/Controllers/PlausibleProxyController.php`
- CSRF: `stats/api/event` en `VerifyCsrfToken::$except`
- `docs/MEASUREMENT.md` (este archivo)
