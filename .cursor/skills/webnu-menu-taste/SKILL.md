---
name: webnu-menu-taste
description: Diseño premium anti-slop para cartas QR móvil Webnu (Laravel Blade, tokens --wn-*). Usar al crear, refinar o revisar plantillas themes (20 QR), hero, tarjetas, front-*.css, menu-hero o cuando el usuario pida mejor diseño de carta móvil. NO aplicar Taste Skill genérico (React/Framer) en este contexto.
---

# Webnu Menu Taste

Skill de criterio estético para las **20 plantillas carta QR** de Webnu. Complementa — nunca sustituye — el estándar inamovible.

## Jerarquía de reglas

1. **[docs/MENU-TEMPLATE-STANDARD.md](../../../docs/MENU-TEMPLATE-STANDARD.md)** — fuente de verdad; manda sobre este skill.
2. **[docs/MENU-HERO-SYSTEM.md](../../../docs/MENU-HERO-SYSTEM.md)** — hero, presets, analyzer, crop.
3. **Este skill** — anti-slop, ritmo visual y pre-flight QA.

Si hay conflicto entre taste y estándar → gana el estándar.

**No usar** el skill personal `design-taste-frontend` (Taste Skill) en cartas Webnu: está pensado para React/Tailwind/Framer Motion.

## Diales Webnu (fijos para carta QR)

| Dial | Valor | Regla |
|------|-------|-------|
| `LEGIBILITY` | **10** | WCAG AA mínimo; texto legible con banner claro, oscuro o mixto |
| `MOTION_INTENSITY` | **1–2** | Solo `transition`/`:active` sutiles (≤200ms); sin parallax, magnetic, GSAP |
| `VISUAL_DENSITY` | **6–7** | Carta escaneable en 2–3 s; platos densos pero con respiro |
| `DECORATION` | **2** | Acentos mínimos en `front-{template}.css`; nunca competir con contenido del cliente |

No pedir al usuario que cambie estos diales salvo petición explícita de más/menos decoración en un tema concreto.

## Anti-slop (adaptado de Taste → Blade/CSS)

### Tipografía

- Escala fija: título plato **1rem / 600**, descripción **0.875rem**, precio destacado pero no gritón.
- Títulos hero con `clamp()`; nunca px fijos sin escala.
- Máximo **2 familias** (`--wn-font-heading`, `--wn-font-body`); no mezclar más fuentes.
- Evitar Inter/system-ui como “solución premium”; respetar fuentes del preset en `company_templates.php`.

### Espaciado

- Múltiplos de **4px / 8px**; usar tokens existentes antes de inventar valores.
- Safe area: `max(1rem, env(safe-area-inset-*))` en márgenes laterales e inferiores.
- Ritmo vertical consistente entre secciones; no huecos aleatorios entre tarjetas.

### Color y materialidad

- Máximo **1 acento** fuerte por plantilla (`--wn-primary` o `--wn-accent`, no ambos compitiendo).
- Sombras tintadas al fondo (`rgba` derivado de `--wn-bg`), no gris genérico `#00000020`.
- Modo claro: sombras suaves; **prohibido** borde negro duro en tarjetas.
- Modo oscuro: `surface` elevada sobre `bg`; bordes `rgba(255,255,255,0.08)` sutiles.
- **Prohibido**: gradientes fijos en hero que ignoren luminancia del banner; overlays dinámicos vía `--wn-hero-*` únicamente.

### Layout (patrones LLM a evitar)

- Hero decorativo centrado sin bloque de marca.
- Grid de 3 columnas idénticas (viewport móvil 320–430px).
- Tarjetas clonadas sin jerarquía (precio/título/badge mal ordenados).
- Decoración (líneas, patrones, **emojis**) que compita con fotos del cliente.
- Scroll horizontal accidental.

### Iconos (obligatorio)

- **Un solo set**: SVG en `resources/views/themes/partials/icons/` — stroke 1.5–2px, `currentColor`.
- **Prohibido**: emojis como iconos, Font Awesome en partials de carta, mezclar librerías.
- Para audits/redesigns de calidad usar [webnu-menu-hallmark](../webnu-menu-hallmark/SKILL.md) (`hallmark audit`, `hallmark redesign`).

### Estados interactivos

- `:active` en botones/chips: `scale(0.98)` o `-translate-y-[1px]` equivalente en CSS puro.
- Favorito/info: touch target **≥44×44px**; estado no solo por color (`aria-pressed`, icono).
- Sin spinner genérico; loading de carta es server-rendered (no aplica skeleton aquí salvo modal).

## Arquitectura obligatoria (no violar)

| Capa | Archivo |
|------|---------|
| Shell | `resources/views/themes/partials/modern-menu-layout.blade.php` |
| Hero | `resources/views/themes/partials/menu-hero.blade.php` |
| Logo | `resources/views/themes/partials/logo-chip.blade.php` |
| Tarjeta | `resources/views/themes/partials/modern-product-card.blade.php` |
| Presets | `config/company_templates.php` → `hero_presets`, `template_hero` |
| CSS base | `public/css/themes/front-menu-ui.css`, `front-menu-hero.css`, `front-modern.css` |
| CSS tema | `public/css/themes/front-{template}.css` — **solo acentos** |
| Variables | `resources/views/themes/partials/theme-vars.blade.php` |

Entrada mínima plantilla: `@include modern-menu-layout` + `variant` + `cardLayout`. **Sin** `heroMode`.

## Workflow del agente

1. **Leer** preset de la plantilla en `config/company_templates.php` → `template_hero`.
2. **Leer** [reference.md](reference.md) si necesitas mapa de las 16 plantillas o prompts.
3. **Editar solo** capas permitidas:
   - `public/css/themes/front-{template}.css` — color, tipografía, acentos decorativos.
   - `theme-vars.blade.php` — solo si falta un token `--wn-*` justificado.
   - Specialty cards (`product-overlay`, `product-temporada`, `product-catalogo`) — alineadas al §6 del estándar.
4. **No tocar** estructura de hero, nav, tarjetas estándar ni shell salvo bug confirmado.
5. **Pre-flight** (obligatorio antes de dar por válido) — copiar checklist y marcar cada ítem:

```
Pre-flight {template}:
- [ ] logo-chip contraste (banner claro/oscuro/mixto)
- [ ] nombre legible en hero (3 condiciones)
- [ ] OK sin banner / sin logo / plato sin foto
- [ ] 1 plato y 15+ platos mantienen ritmo
- [ ] precios largos OK en 320px
- [ ] nav sticky no tapa primer plato
- [ ] sin scroll horizontal 320px
- [ ] front-{template}.css sin CSS estructural
- [ ] preview /carta/demo?tpl={template} 375px y 320px
```

6. Si algún ítem falla → **no válido**; corregir antes de cerrar.

## Prohibiciones explícitas

- Framer Motion, GSAP, magnetic buttons, parallax tilt, liquid glass excesivo.
- Tailwind, React, nuevas dependencias JS para diseño carta.
- Layout hero ad hoc fuera de `menu-hero.blade.php`.
- Logo sin `logo-chip`.
- Overlays de banner fijos (misma opacidad para toda foto).
- CSS estructural (hero, cards, nav) en `front-{template}.css`.
- Parámetro `heroMode` en blades raíz.
- **Emojis** en markup, CSS, demo o badges visibles.
- **Font Awesome** en partials de carta (usar SVG en `partials/icons/`).

## Verificación local

```bash
.\run-local.ps1
# http://127.0.0.1:8000/carta/demo?tpl={template}
# Demos curadas: /carta/demo-cocktails (nocturne)
```

## Recursos

- Estándar completo: [docs/MENU-TEMPLATE-STANDARD.md](../../../docs/MENU-TEMPLATE-STANDARD.md)
- Mapa plantillas y prompts: [reference.md](reference.md)
- Hallmark adaptado: [webnu-menu-hallmark](../webnu-menu-hallmark/SKILL.md)
