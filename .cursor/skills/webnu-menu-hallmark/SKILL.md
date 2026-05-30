---
name: webnu-menu-hallmark
description: Anti-slop Hallmark adaptado a cartas QR móvil Webnu (Blade, tokens --wn-*). Usar en audit/redesign/study de plantillas, refinado visual y revisión de calidad. Prohibido emojis; solo iconos SVG. NO aplicar Hallmark genérico (React/Tailwind) sin este adaptador.
---

# Webnu Menu Hallmark

Adaptador de [Hallmark](https://github.com/nutlope/hallmark) para las **20 plantillas carta QR** de Webnu. Complementa `webnu-menu-taste` con audits, redesigns y extracción de DNA de referencias.

## Jerarquía de reglas

1. **[docs/MENU-TEMPLATE-STANDARD.md](../../../docs/MENU-TEMPLATE-STANDARD.md)** — fuente de verdad; manda sobre todo.
2. **[docs/MENU-HERO-SYSTEM.md](../../../docs/MENU-HERO-SYSTEM.md)** — hero, presets, analyzer.
3. **Este skill** — Hallmark adaptado + anti-slop operativo.
4. **[webnu-menu-taste](../webnu-menu-taste/SKILL.md)** — diales y pre-flight carta QR.
5. **Hallmark personal** (`~/.agents/skills/hallmark/`) — referencia para slop-test y anti-patterns; no aplicar React/Tailwind/Framer directamente.

Si hay conflicto entre Hallmark genérico y Webnu → gana Webnu.

## Verbos Hallmark en Webnu

| Invocación | Acción en Webnu |
|------------|-----------------|
| `hallmark audit {template}` | Leer `front-{template}.css` + partials usados. Punch list rankeada. **No editar.** |
| `hallmark redesign {template}` | Rediseño visual **solo** en capas permitidas: `front-{template}.css`, tokens `--wn-*`, specialty cards alineadas al §6 del estándar. **No** cambiar preset hero, shell, nav structure. |
| `hallmark study {url\|screenshot}` | Extraer DNA (macrostructure, tipografía, color anchor). Emitir diagnóstico. **No** clonar píxeles ni templates de pago. Usar DNA para futura plantilla respetando arquitectura Webnu. |
| *(default build)* | Nueva plantilla o refinado mayor: elegir preset + cardLayout distintivos, aplicar slop-test, pre-emit critique. |

## Prohibiciones explícitas (Webnu)

- **Emojis** en markup, CSS, demo, badges, placeholders o copy generado. Usar texto o iconos SVG.
- **Iconos mezclados**: un solo set — [`resources/views/themes/partials/icons/`](../../../resources/views/themes/partials/icons/). Stroke 1.5–2px, `currentColor`. No Font Awesome en partials de carta.
- **Hallmark genérico sin adaptar**: no Tailwind, no React, no Framer Motion, no inventar rutas/componentes nuevos.
- **CSS estructural** en `front-{template}.css` salvo excepciones documentadas (nocturne overlay, otaku, etc.) — preferir acentos; cambios estructurales requieren justificación y alineación al estándar.

## Pre-emit (obligatorio antes de cerrar)

Copiar y marcar:

```
Pre-emit {template}:
- [ ] Sin emojis en UI ni demo
- [ ] Solo iconos SVG (no FA mezclado)
- [ ] logo-chip contraste OK
- [ ] hero legible (claro/oscuro/mixto)
- [ ] OK sin banner / sin logo / plato sin foto
- [ ] 1 plato y 15+ platos mantienen ritmo
- [ ] precios largos OK 320px
- [ ] nav sticky no tapa primer plato
- [ ] sin scroll horizontal 320px
- [ ] preview /carta/demo?tpl={template} 375px y 320px
- [ ] Hallmark slop gates: no Inter-default, no emoji-icon (gate 60), no mixed icon sets
```

## Workflow audit

1. Leer preset en `config/company_templates.php` → `template_hero`.
2. Leer `front-{template}.css` y partials del variant.
3. Puntuar contra [reference.md](reference.md) (anti-patterns Hallmark + Webnu).
4. Devolver punch list: crítico / alto / medio. Sin edits.

## Workflow redesign

1. Audit primero (o resumir si el usuario ya auditó).
2. Confirmar archivos a tocar (lista explícita).
3. Editar solo capas permitidas; preservar copy, IA, rutas, preset hero.
4. Pre-emit checklist + preview demo.

## Recursos

- Hallmark instalado: `~/.agents/skills/hallmark/`
- Mapa plantillas: [webnu-menu-taste/reference.md](../webnu-menu-taste/reference.md)
- Slop gates extracto: [reference.md](reference.md)
- Estándar: [docs/MENU-TEMPLATE-STANDARD.md](../../../docs/MENU-TEMPLATE-STANDARD.md)
