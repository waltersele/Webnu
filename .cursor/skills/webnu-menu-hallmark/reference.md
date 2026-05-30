# Referencia — webnu-menu-hallmark

Extracto de Hallmark (`~/.agents/skills/hallmark/references/`) adaptado a carta QR Webnu.

## Gates slop-test relevantes (carta móvil)

| Gate | Regla |
|------|-------|
| 36 | Sin scroll horizontal en 320px |
| 46–50 | Contraste WCAG, microinteracciones sutiles |
| 60 | **Sin emoji como icono** (✨ 🚀 🔥 en badges, features, steps) |
| — | **Sin icon sets mezclados** (FA + SVG + emoji en la misma carta) |

## Anti-patterns Hallmark → Webnu

| Anti-pattern | Fix en Webnu |
|--------------|--------------|
| Generic emoji as feature icon | SVG en `partials/icons/` o tipografía sola |
| Mismatched icon sets | Solo SVG partials; eliminar `fas fa-*` en themes |
| Inter + purple gradient default | Respetar `--wn-font-*` y preset de `company_templates.php` |
| Three equal feature columns | Variar cardLayout (stacked/horizontal/overlay) por plantilla |
| Identical hero → cards → footer rhythm | Usar presets distintos: typographic_dark, overlay, circle_emblem, etc. |

## Archivos audit/redesign

| Capa | Path | Editable en redesign |
|------|------|----------------------|
| CSS tema | `public/css/themes/front-{template}.css` | Sí (acentos; estructura solo si estándar lo permite) |
| Preset | `config/company_templates.php` | No (bloqueado) |
| Shell | `modern-menu-layout.blade.php` | No salvo bug |
| Iconos | `partials/icons/svg-*.blade.php` | Sí (nuevos iconos) |
| Demo | `DemoCompanyDataProvider.php`, `seed-local-demo.php` | Sí (sin emojis) |

## Iconos SVG — catálogo actual

| Partial | Uso |
|---------|-----|
| svg-info | Botón info flotante |
| svg-heart | Favoritos |
| svg-chevron-right | Detalle plato |
| svg-cocktail | Sección nocturne |
| svg-utensils | Sección temporada / placeholder |
| svg-phone, svg-mobile, svg-envelope, svg-globe | Footer contacto |
| svg-clock, svg-map-pin | Footer meta |
| svg-times | Cerrar modal/drawer |
| svg-expand | Overlay / reel fullscreen |
| svg-play, svg-video, svg-camera | Media triggers |
| svg-lumiere-diamond, svg-fastfood-bolt, svg-saffron-leaf, svg-velvet-wine, svg-atelier-mark, svg-maison-mark | Iconos de sección por plantilla |

## Comandos útiles

```bash
.\run-local.ps1
# http://127.0.0.1:8000/carta/demo?tpl={template}
```

Hallmark personal: `~/.agents/skills/hallmark/SKILL.md`
