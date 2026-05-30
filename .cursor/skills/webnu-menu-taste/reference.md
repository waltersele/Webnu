# Referencia — webnu-menu-taste

Extracto de [docs/MENU-TEMPLATE-STANDARD.md](../../../docs/MENU-TEMPLATE-STANDARD.md) para consulta rápida del agente.

## Archivos clave

| Área | Path |
|------|------|
| Estándar completo | `docs/MENU-TEMPLATE-STANDARD.md` |
| Sistema hero | `docs/MENU-HERO-SYSTEM.md` |
| Presets / defaults | `config/company_templates.php` |
| Shell layout | `resources/views/themes/partials/modern-menu-layout.blade.php` |
| Hero unificado | `resources/views/themes/partials/menu-hero.blade.php` |
| Logo chip | `resources/views/themes/partials/logo-chip.blade.php` |
| Header compacto | `resources/views/themes/partials/modern-header.blade.php` |
| Tarjeta estándar | `resources/views/themes/partials/modern-product-card.blade.php` |
| Tokens CSS | `resources/views/themes/partials/theme-vars.blade.php` |
| CSS base | `public/css/themes/front-menu-ui.css`, `front-menu-hero.css`, `front-modern.css` |
| CSS por tema | `public/css/themes/front-{template}.css` |
| Analyzer banner | `app/Services/BannerImageAnalyzer.php` |
| Analyzer logo | `app/Services/LogoColorAnalyzer.php` |

## Presets hero (bloqueados)

| Preset | Ratio crop | Bleed | Logo | Chef |
|--------|------------|-------|------|------|
| `dark_bleed` | 16:9 | sí | rounded | sí |
| `compact_card` | 4:3 | no | rounded | sí |
| `circle_emblem` | 16:9 | sí | circle | sí |
| `spotlight_dish` | 16:9 | no | rounded | no |
| `typographic_dark` | 16:9 | sí | rounded | no |
| `minimal_bar` | 16:9 | no | rounded | no |

## Mapa de las 16 plantillas

| Template | Preset | cardLayout | Nav | Tarjeta specialty |
|----------|--------|------------|-----|-------------------|
| lumiere | dark_bleed | stacked | chips | modern |
| otaku | dark_bleed | stacked | chips | modern |
| japo | dark_bleed | stacked | chips | modern |
| fastfood | dark_bleed | stacked / horizontal | chips | modern |
| asador | dark_bleed | stacked | chips | modern |
| oriental | dark_bleed | stacked | chips | modern |
| basic | compact_card | horizontal | chips | modern |
| pasion | compact_card | stacked | chips | modern |
| temporada | compact_card | stacked | sticky | product-temporada |
| pizza | compact_card | stacked | chips | modern |
| mar | compact_card | stacked | chips | modern |
| visual | compact_card | horizontal | chips | modern |
| elegance | circle_emblem | stacked | chips | modern |
| bistro | spotlight_dish | horizontal | chips light | modern |
| nocturne | typographic_dark | stacked | sticky | product-overlay |
| catalogo | minimal_bar | horizontal | sticky | product-catalogo |
| saffron | circle_emblem | horizontal | chips light | modern |
| velvet | dark_bleed | stacked | chips | modern |
| atelier | typographic_dark | overlay (todos) | sticky | product-overlay |
| maison | typographic_dark | stacked + overlay estrella | sticky | product-overlay |

## Tokens CSS obligatorios

```css
/* Tema (personalizable) */
--wn-primary, --wn-accent, --wn-bg, --wn-surface, --wn-text, --wn-text-muted
--wn-font-heading, --wn-font-body

/* Layout */
--wn-header-height: 52px
--wn-nav-height: 52px
--wn-scroll-offset
--wn-header-tone: light | dark

/* Hero (servidor + recorte) */
--wn-hero-overlay-strength   /* 0.45 – 0.92 */
--wn-hero-overlay-mode       /* dark | light */
--wn-hero-text-tone          /* light | dark */
--wn-hero-focal-x, --wn-hero-focal-y

/* Tarjetas */
--wn-card-radius: 16px
--wn-card-shadow: 0 4px 20px rgba(15, 23, 42, 0.08)
```

## Prompt estándar (generar / revisar)

```text
Eres diseñador/implementador móvil-first de cartas QR Webnu.
Objetivo: carta premium, siempre legible, calidad de construcción — no plantilla genérica.

CONTEXTO TÉCNICO (obligatorio)
- Shell: modern-menu-layout + menu-hero + logo-chip + modern-product-card.
- Preset hero BLOQUEADO en config/company_templates.php (hero_presets + template_hero).
- Contraste banner en SERVIDOR (BannerImageAnalyzer → CSS vars --wn-hero-*).
- Logo SIEMPRE en logo-chip (light/dark/glass); nunca logo crudo.
- Colores/fuentes personalizables; estructura hero NO.
- Viewport: 320–430px; safe-area iOS; scroll vertical.

PRINCIPIOS INAMOVIBLES
1. Legibilidad primero: ningún texto pierde contraste contra foto o marca.
2. Jerarquía: marca → categorías → plato → precio → detalle.
3. Tokens unificados (--wn-card-radius, --wn-card-shadow, tipografía, espaciado).
4. Robustez: logo blanco/negro, banner claro/oscuro, sin foto, nombres largos.
5. Cero decoración que compita con contenido del cliente.

DoD: pasar checklist §10 de docs/MENU-TEMPLATE-STANDARD.md.
Preview: /carta/demo?tpl={template} en móvil 375px y 320px.

PROHIBIDO: heroMode manual, logo sin chip, overlays fijos, CSS estructural por plantilla.
```

## Prompt corto (revisión rápida)

```text
¿Cumple estándar Webnu premium?
□ logo-chip con contraste □ hero overlay por luminancia □ bloque marca completo
□ texto light/dark correcto □ tarjetas ratio/sombra/touch 44px
□ nav sin tapar contenido □ OK sin logo/banner/foto
□ solo tokens --wn-* y menu-hero □ preview /carta/demo?tpl=X en 320px
Si algún □ falla → no válido.
```

## Preview local

```bash
.\run-local.ps1
http://127.0.0.1:8000/carta/demo?tpl=lumiere
http://127.0.0.1:8000/carta/demo-cocktails   # nocturne curada
```

## Procedimiento plantilla nueva

1. Entrada en `config/company_templates.php` → `templates`, `defaults`, `template_hero`.
2. Blade raíz mínimo → solo `modern-menu-layout`.
3. `front-{nombre}.css` → solo acentos.
4. SVG preview admin en `public/img/admin/templates/`.
5. Pasar checklist DoD y preview `?tpl={nombre}`.
