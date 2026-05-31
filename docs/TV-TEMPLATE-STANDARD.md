# Estándar de plantillas TV (16:9 / TVPik)

Documento de referencia para diseñar, implementar y revisar las **15 plantillas TV** de Webnu. Todo template nuevo o refactor debe cumplir estas condiciones.

Relacionado: [MENU-TEMPLATE-STANDARD.md](MENU-TEMPLATE-STANDARD.md) · [TVPIK-INTEGRATION.md](TVPIK-INTEGRATION.md) · [TV-TEMPLATES-PREMIUM-PROPOSAL.md](TV-TEMPLATES-PREMIUM-PROPOSAL.md)

---

## 1. Objetivo

Pantallas TV **legibles a distancia**, **16:9**, con identidad de marca coherente con la carta QR. El restaurante personaliza colores y fuentes en **Mi carta → Personalización**; las plantillas TV deben **consumir esos tokens**, no ignorarlos.

Hay **15 plantillas TV** registradas en `config/tvpik_templates.php`.

---

## 2. Arquitectura obligatoria

Toda plantilla TV usa el shell compartido. **Prohibido** HTML/CSS sueltos fuera de esta estructura.

| Capa | Archivo / config | Función |
|------|------------------|---------|
| Presenter | `app/Services/TvMenuPresenter.php` | Datos: secciones, platos, logo, **tokens de tema** |
| Layout shell | `resources/views/tv/layout.blade.php` | `<html>`, header, frame, inyección CSS vars |
| Vista plantilla | `resources/views/tv/templates/{layout}.blade.php` | Solo `@extends('tv.layout')` + bloque `tv_content` |
| Partials | `resources/views/tv/partials/` | Header, background, scripts compartidos |
| CSS | `public/css/webnu-tv.css` | Bloque `.wn-tv--{layout}` por plantilla |
| JS rotación | `public/js/webnu-tv.js` | Carruseles, dots, `initRotate()` |
| Registro | `config/tvpik_templates.php` | Catálogo, metadatos, `rotate_seconds`, `show_header` |
| Registry | `App\Services\Tv\TvTemplateRegistry` | Resolución layout → vista |
| Rutas | `GET /tv/{slug}/{layout}` | Preview y reproductor (`?preview=1`, `?player=1`) |

### Entrada mínima de una plantilla

```blade
@extends('tv.layout')

@section('tv_content')
    <div class="wn-tv-{layout}" data-tv-rotate="{{ $rotateSeconds ?? 0 }}">
        {{-- contenido --}}
    </div>
@endsection
```

**No** duplicar `<html>`, `<head>` ni header: el layout ya los incluye.

---

## 3. Personalización desde el backend

### 3.1 Dónde edita el cliente

**Mi carta → Personalización → Colores y tipografía** (`studio-step-design.blade.php`).

Se guarda en `companies.theme_settings` vía `CompanyThemeService`:

| Clave admin | Etiqueta | Uso carta QR |
|-------------|----------|--------------|
| `primary` | Color principal | `--wn-primary` |
| `accent` | Color de acento | `--wn-accent` |
| `background` | Fondo | `--wn-bg` |
| `surface` | Tarjetas | `--wn-surface` |
| `text` | Texto | `--wn-text` |
| `text_muted` | Texto secundario | `--wn-text-muted` |
| `font_heading` | Fuente títulos | `--wn-font-heading` |
| `font_body` | Fuente textos | `--wn-font-body` |

Defaults por plantilla QR: `config/company_templates.php` → `defaults.{template}`.

### 3.2 Qué llega a la TV

`TvMenuPresenter` expone todos los tokens al layout vía `themeTokens()`:

| `theme_settings` | Variable Blade | CSS var inyectada |
|------------------|----------------|-------------------|
| `primary` | `$accent` | `--wn-tv-accent` |
| `accent` | `$themeAccent` | `--wn-tv-theme-accent` |
| `background` | `$themeBg` | `--wn-tv-bg` |
| `surface` | `$themeSurface` | `--wn-tv-surface` |
| `text` | `$themeText` | `--wn-tv-text` |
| `text_muted` | `$themeTextMuted` | `--wn-tv-text-muted` |
| `font_heading` | `$themeFontDisplay` | `--wn-tv-font-display` |
| `font_body` | `$themeFontBody` | `--wn-tv-font-body` |
| *(calculado)* | `$themeBadgeFg` | `--wn-tv-badge-fg` |

Inyección en `resources/views/tv/partials/theme-vars.blade.php` (incluido desde `tv/layout.blade.php`).

Alias derivados en CSS:

```css
--wn-tv-badge-bg: var(--wn-tv-theme-accent);
--wn-tv-price: var(--wn-tv-accent);
--wn-tv-dot-active: var(--wn-tv-accent);
```

`--wn-tv-badge-fg` se calcula por luminancia del acento (texto claro u oscuro según contraste).

Las plantillas **legacy** (nivel C) pueden seguir ignorando estos tokens hasta migrarse; las **nuevas** deben consumirlos (§5).

### 3.3 Objetivo del estándar (plantillas nuevas)

Toda plantilla **nueva** o **refactor** debe consumir los tokens globales (§4). El presenter ya los inyecta; falta migrar plantillas legacy (§15).

**Regla de producto:** el cliente no configura colores TV por separado. Los colores de Personalización son la **única fuente de verdad** para carta y pantallas.

---

## 4. Tokens CSS obligatorios (TV)

Definidos en `layout.blade.php` (global) y extendidos por plantilla bajo `.wn-tv--{layout}`.

### 4.1 Tokens globales (layout)

```css
/* Marca — personalizables (inyectados desde theme_settings) */
--wn-tv-accent          /* primary: precios, highlights, dots activos */
--wn-tv-theme-accent    /* accent: badges, sellos, bordes de acento */
--wn-tv-bg              /* background del local */
--wn-tv-surface         /* paneles, tarjetas */
--wn-tv-text            /* texto principal */
--wn-tv-text-muted      /* secundario, kicker, meta */
--wn-tv-font-display    /* títulos grandes TV */
--wn-tv-font-body       /* listas, descripciones */

/* Derivados (calculados en layout o CSS, no hardcode) */
--wn-tv-badge-bg        /* theme-accent con fallback */
--wn-tv-badge-fg        /* contraste sobre badge-bg */
--wn-tv-price           /* alias → accent o theme-accent según plantilla */
--wn-tv-dot-active      /* alias → accent o theme-accent */

/* Layout fijo TV */
--wn-tv-header-h        /* altura header si show_header */
--wn-tv-safe-x          /* clamp horizontal safe area */
```

### 4.2 Tokens locales (solo si la plantilla lo justifica)

Bajo `.wn-tv--{layout}` se pueden definir alias semánticos que **referencien** tokens globales:

```css
.wn-tv--tapas {
  --wn-tv-tapas-accent: var(--wn-tv-theme-accent);
  --wn-tv-tapas-ink: var(--wn-tv-text, var(--wn-tv-accent));
}
```

**Prohibido** definir `#e9a233`, `#0b1f3a`, etc. como color de marca sin pasar por `var(--wn-tv-*)`.

### 4.3 Mapeo semántico recomendado

| Elemento UI | Token preferido |
|-------------|-----------------|
| Precio | `--wn-tv-accent` o `--wn-tv-price` |
| Badge / sello / kicker acento | `--wn-tv-theme-accent` |
| Fondo pantalla | `--wn-tv-bg` (+ gradiente con `color-mix`) |
| Panel / tarjeta | `--wn-tv-surface` |
| Título plato | `--wn-tv-text` |
| Descripción / meta | `--wn-tv-text-muted` |
| Dot carrusel activo | `--wn-tv-dot-active` → `--wn-tv-accent` |
| Borde acento | `--wn-tv-theme-accent` |
| Header modo pill | `--wn-tv-accent` |

### 4.4 Contraste y badges

- Texto sobre `--wn-tv-theme-accent`: usar `--wn-tv-badge-fg` (futuro: cálculo automático como `LogoColorAnalyzer`).
- Si `theme-accent` es claro (ej. crema Maison), **nunca** poner texto claro sobre badge — validar con demo-maison.
- Mínimo **WCAG AA** en precios y títulos sobre fondo TV.

---

## 5. Niveles de integración con el tema

Todas las plantillas del catálogo deben mantenerse en **nivel A**: fondo, texto, acentos, badges y fuentes consumen tokens globales de `theme-vars.blade.php`.

| Nivel | Criterio |
|-------|----------|
| **A — Completa** (obligatorio) | `--wn-tv-bg`, `--wn-tv-text`, `--wn-tv-accent`, `--wn-tv-theme-accent`, fuentes del local |
| ~~B — Parcial~~ | Obsoleto — migrar a A |
| ~~C — Legacy~~ | Obsoleto — prohibido en plantillas activas |

**Plantillas nuevas y refactors:** solo nivel **A**.

---

## 6. Reglas de layout TV

- Viewport: **1920×1080** (referencia); probar también **1280×720**.
- Tipografía: `clamp()` en títulos y precios; nada por debajo de **1rem** en precios legibles a 3 m.
- Safe area: márgenes `clamp(2rem, 3vw, 4rem)`; contenido crítico no en bordes.
- Rotación: usar `data-tv-rotate="{{ $rotateSeconds }}"` + clase `.is-active` en slides; dots con `.wn-tv-dot`.
- Header: respetar `$showHeader` del registro; no renderizar header duplicado en la plantilla.
- Logo: usar `$logoUrl` del presenter; fallback tipográfico con `$company->name`.
- Sin contenido: mensaje en `.wn-tv-empty`, centrado.
- Player mode: compatible con `?player=1` y HUD existente; no romper altura `100vh`.

---

## 7. CSS por plantilla

- Todo el CSS de una plantilla va en **un bloque** `.wn-tv--{layout}` en `webnu-tv.css`.
- Clases BEM: `.wn-tv-{layout}__elemento--modificador`.
- Animaciones permitidas: `opacity`, `transform`, Ken Burns en imágenes, `@keyframes` CSS puro.
- **Prohibido** JS inline para estilos; scripts en `@stack('tv_scripts')` o `webnu-tv.js`.
- Miniatura: SVG en `public/img/tvpik/previews/{layout}.svg` (captura real recomendada para premium).

---

## 8. Registro en config

Entrada mínima en `config/tvpik_templates.php`:

```php
'nombre' => [
    'key' => 'nombre',
    'label' => '…',
    'description' => '…',
    'category' => 'restaurant',
    'layout' => 'nombre',           // = clase body wn-tv--nombre
    'view' => 'tv.templates.nombre',
    'rotate_seconds' => 12,
    'show_header' => true,
    'icon' => 'ti-…',
    'thumbnail' => 'img/tvpik/previews/nombre.svg',
    // 'premium' => true,
    // 'supports_menu_selector' => true,
],
```

Añadir `layout` a array `layouts` del mismo config.

Checklist integración:

1. Vista Blade en `resources/views/tv/templates/`.
2. CSS en `webnu-tv.css`.
3. Preview SVG.
4. `MenuSyncService::tvUrls()` ya itera el catálogo → URL automática en API.
5. Si `premium => true`: gating en `TvpikPublishService` + test en `TvpikPremiumTemplateAccessTest`.

---

## 9. Definition of Done (checklist QA)

Antes de dar por válida una plantilla TV:

- [ ] Usa `@extends('tv.layout')` sin HTML duplicado.
- [ ] Colores de marca vía `var(--wn-tv-*)`, no hex de marca sueltos (nivel A).
- [ ] Cambiar **primary** y **accent** en Personalización altera precios/badges/acentos visibles.
- [ ] Badge legible con acento claro y acento oscuro (demo-maison).
- [ ] OK con 0 platos, 1 plato, 20+ platos / secciones largas.
- [ ] Plato sin imagen: placeholder elegante.
- [ ] Nombres largos y precios "Consultar" no desbordan.
- [ ] Rotación y dots funcionan si `rotate_seconds > 0`.
- [ ] Preview: `http://127.0.0.1:8000/tv/demo-maison/{layout}?preview=1`.
- [ ] Player: `?player=1` sin scroll ni barras rotas.
- [ ] Registrada en `tvpik_templates.php` + SVG preview.
- [ ] Documentado nivel de integración tema (A/B/C) en PR o tabla §14.

---

## 10. Prohibiciones explícitas

1. Colores de marca hardcodeados (`#e9a233`, `#0b1f3a`, `#c4a574`) sin token.
2. Layout HTML propio fuera de `tv.layout`.
3. Duplicar header/logo cuando `show_header => true`.
4. Depender de fuentes solo en CSS sin mapear `font_heading` / `font_body` (cuando el presenter las exponga).
5. Texto sobre foto sin sombra/viñeta cuando el contraste no está garantizado.
6. Crear plantilla TV sin entrada en `tvpik_templates.php`.
7. Confundir plantilla QR (`company.template`) con plantilla TV (`template_key`).

---

## 11. Prompt estándar (agente / diseño / IA)

Copiar y usar tal cual al generar o revisar plantillas TV:

```text
Eres diseñador/implementador de plantillas TV 16:9 para Webnu (TVPik).

CONTEXTO TÉCNICO (obligatorio)
- Shell: tv.layout + TvMenuPresenter + webnu-tv.css bajo .wn-tv--{layout}.
- Personalización: theme_settings del negocio (Mi carta → Personalización).
- Tokens TV: --wn-tv-accent (primary), --wn-tv-theme-accent (accent), --wn-tv-bg,
  --wn-tv-text, --wn-tv-text-muted, --wn-tv-font-display/body.
- Nunca hex de marca sueltos; usar var() con fallback neutro.
- Referencia carta QR: docs/MENU-TEMPLATE-STANDARD.md (misma fuente theme_settings).

PRINCIPIOS
1. Legibilidad a distancia (3 m): precios y títulos grandes, contraste AA.
2. Coherencia marca: colores del admin deben notarse en la composición (nivel A).
3. Robustez: sin foto, nombres largos, 1 plato, muchas secciones.
4. Estructura bloqueada; cliente solo personaliza colores/fuentes/logo.

CSS
- Bloque .wn-tv--{layout} en webnu-tv.css.
- Alias locales (--wn-tv-{layout}-*) solo si referencian tokens globales.
- Animaciones: fade, Ken Burns, CSS puro.

DoD: checklist §9 de docs/TV-TEMPLATE-STANDARD.md.
Preview: /tv/demo-maison/{layout}?preview=1
Probar: cambiar primary/accent en Personalización y verificar reflejo visual.

PROHIBIDO: navy/gold fijos, layout HTML propio, ignorar theme_settings.
```

---

## 12. Prompt corto (revisión rápida)

```text
¿Cumple estándar Webnu TV?
□ @extends tv.layout □ tokens --wn-tv-* (no hex marca)
□ primary/accent visibles al cambiar Personalización
□ badge legible con acento claro (demo-maison)
□ OK sin foto / 1 plato / nombres largos
□ preview /tv/demo-maison/{layout}?preview=1 en 1920×1080
Si algún □ falla → no válido.
```

---

## 13. Añadir plantilla nueva (procedimiento)

1. Diseño + nivel A/B/C objetivo (nuevas → **A**).
2. Entrada en `config/tvpik_templates.php` (`templates` + `layouts`).
3. `resources/views/tv/templates/{layout}.blade.php`.
4. CSS `.wn-tv--{layout}` en `public/css/webnu-tv.css`.
5. SVG `public/img/tvpik/previews/{layout}.svg`.
6. Si hace falta ampliar tokens: actualizar `TvMenuPresenter` + `layout.blade.php` en el mismo PR.
7. Pasar checklist §9.
8. Actualizar tabla §14 y enlaces en `TVPIK-INTEGRATION.md`.

---

## 14. Presenter e inyección de tokens (implementado)

`TvMenuPresenter::themeTokens()` resuelve `theme_settings` + defaults y pasa al layout:

- `accent`, `themeAccent`, `themeBg`, `themeSurface`, `themeText`, `themeTextMuted`
- `themeFontDisplay`, `themeFontBody` vía `Company::themeFontFamily()`
- `themeBadgeFg` vía luminancia del acento (`contrastingTextColor()`)

Vista: `resources/views/tv/partials/theme-vars.blade.php` · CSS base: `html, body` usan `--wn-tv-bg` y `--wn-tv-text`.

**Migración completada (2026-05):** las 15 plantillas usan tokens globales. Mantener al añadir CSS nuevo.

---

## 15. Mapa de plantillas (nivel A)

| Layout | Label | Estado tema |
|--------|-------|-------------|
| menu | Carta completa | A |
| spotlight | Plato del día | A |
| featured | Destacados | A |
| video | Vídeos | A |
| menu_video | Carta + vídeos | A |
| menu_banner | Carta con banner | A |
| daily | Menú del día | A |
| hero | Plato hero | A |
| tapas | Tapas / Destacados | A |
| cinema | Cinema | A |
| sommelier | Sommelier | A |
| degustacion | Degustación | A |
| signature | Firma del chef | A |
| lounge | Lounge | A |
| marquee | Marquee | A |

Viñetas sobre fotos y sombras estructurales pueden usar negro semitransparente; colores de **marca** siempre vía `--wn-tv-*`.

---

## 16. Verificación local

```powershell
.\run-local.ps1
```

```
http://127.0.0.1:8000/tv/demo-maison/tapas?preview=1
http://127.0.0.1:8000/tv/demo-maison/signature?preview=1
```

Admin → Personalización → cambiar colores → refrescar preview TV.

Demo: `demo@webnu.local` / `demo123` · Carta Maison: `/carta/demo-maison`

---

*Última revisión: estándar TV alineado con theme_settings de carta QR y catálogo en `config/tvpik_templates.php`.*
