# Medición unificada (Webnu)

Loader compartido: `public/js/measurement.js` (idéntico al de Shoow). Consent Mode v2 + Plausible vía proxy same-origin + marketing solo con consentimiento de marketing.

## Tres capas

| Capa | Qué | Consentimiento |
|------|-----|----------------|
| **1 — Exempt** | Plausible (script + API vía `/stats/...`) | No requiere banner; sin cookies de identificación |
| **2 — Google Consent Mode** | gtag o GTM con `consent default denied` | `loadGoogleBeforeConsent: true` carga el script en denied (modelado); cookies `_ga` solo tras grant |
| **3 — Marketing** | Meta Pixel, LinkedIn Insight | Solo si `marketing === true` |

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
- Payload: `{ v: 1, necessary, analytics, marketing, updatedAt }`. Si `v !== 1`, se vuelve a preguntar.
- Legacy `'accepted'` / `'rejected'` → analytics true/false, marketing false.

## Banner

Tres botones: **Rechazar** | **Guardar selección** | **Aceptar todas**. Checkboxes Analítica / Marketing desmarcados por defecto. Footer y legal: enlace `data-manage-cookies`.

## Cómo añadir una herramienta

1. **Analítica (capa 2):** campo en admin + `consented.*` + en JS cargar solo si `consent.analytics` (Clarity) o vía Consent Mode (Google).
2. **Marketing (capa 3):** campo en admin + cargar **solo** si `consent.marketing === true`.
3. **Exempt (capa 1):** solo herramientas sin cookies de identificación; URLs en `exempt.*`.

## Proxy Plausible

- `GET /stats/js/script.js` → `{PLAUSIBLE_UPSTREAM_URL}/js/script.js`
- `POST|GET /stats/api/event` → `{upstream}/api/event` (CSRF except)
- Si upstream vacío → 404; el loader no inserta Capa 1.

Variables (`.env` / Admin):

- `PLAUSIBLE_DOMAIN`, `PLAUSIBLE_UPSTREAM_URL` (p. ej. `https://plausible.io`)
- `PLAUSIBLE_SCRIPT_URL`, `PLAUSIBLE_API_URL` (defaults `/stats/...`)
- `MEASUREMENT_LOAD_GOOGLE_BEFORE_CONSENT=true`

## Admin

Tarjeta **Medición y Search Console** incluye `measurement_section=1`. Solo si ese campo llega en el POST se actualizan keys de medición.

## Checklist DevTools (aceptación)

Con `PLAUSIBLE_UPSTREAM_URL` configurado:

1. Banner sin tocar: hits a `/stats/api/event`; cero cookies `_ga*`.
2. Con `loadGoogleBeforeConsent: true`: gtag/GTM carga; `/g/collect` con consentimiento denegado (`gcs=G100`); sin `_ga`.
3. Aceptar todas: `gcs=G111`; cookies `_ga`.
4. Solo analytics + Guardar: analytics granted, ads denied; sin `doubleclick` / `ccm/collect`.
5. Rechazar: consentimiento denied; Plausible sigue.
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
