# Estándar de plantillas carta móvil (QR)

Documento de referencia **inamovible** para diseñar, implementar y revisar las 16 plantillas QR de Webnu. Todo template nuevo o refactor debe cumplir estas condiciones sin excepción.

Relacionado: [MENU-HERO-SYSTEM.md](MENU-HERO-SYSTEM.md) · [MENU-FAVORITES.md](MENU-FAVORITES.md)

---

## 1. Objetivo

Carta móvil **premium**, **siempre legible** y con **calidad de construcción** perceptible: jerarquía clara, contraste garantizado, tokens unificados y comportamiento robusto con contenido real del cliente (logos claros/oscuros, banners variados, platos sin foto, nombres largos).

El cliente personaliza **colores, fuentes, logo, banner y textos**. La **estructura** (preset de hero, layout de tarjetas, navegación) está **bloqueada por plantilla**.

Hay **20 plantillas** QR registradas (ver mapa §14).

---

## 2. Arquitectura obligatoria

Toda plantilla QR debe usar el shell compartido. **Prohibido** layouts propios salvo variantes de tarjeta ya registradas (`product-overlay`, `product-temporada`, `product-catalogo`).

| Capa | Archivo / config | Función |
|------|------------------|---------|
| Shell | `resources/views/themes/partials/modern-menu-layout.blade.php` | Estructura, nav, secciones, tarjetas |
| Hero | `resources/views/themes/partials/menu-hero.blade.php` | Cabecera unificada por preset |
| Logo | `resources/views/themes/partials/logo-chip.blade.php` | Contraste automático (chip) |
| Header compacto | `resources/views/themes/partials/modern-header.blade.php` | Preset `minimal_bar` |
| Tarjeta | `resources/views/themes/partials/modern-product-card.blade.php` | Plato estándar |
| Presets | `config/company_templates.php` → `hero_presets`, `template_hero` | Preset bloqueado por plantilla |
| Banner | `App\Services\BannerImageAnalyzer` | Overlay en servidor |
| Logo | `App\Services\LogoColorAnalyzer` | Variante chip en servidor |
| CSS base | `public/css/themes/front-menu-ui.css`, `front-menu-hero.css`, `front-modern.css` | Tokens y estructura |
| CSS tema | `public/css/themes/front-{template}.css` | **Solo** acentos color/tipografía |
| Variables | `resources/views/themes/partials/theme-vars.blade.php` | Tokens `--wn-*` |
| Vista raíz | `resources/views/themes/{template}.blade.php` | Solo `@include modern-menu-layout` + `variant` + `cardLayout` |

### Entrada mínima de una plantilla

```blade
@include('themes.partials.head')

@include('themes.partials.modern-menu-layout', [
    'variant' => 'lumiere',
    'cardLayout' => 'stacked', // o 'horizontal'
])

@include('themes.partials.modern-scripts')
</body>
</html>
```

**No** pasar `heroMode` manual. El preset viene de `config('company_templates.template_hero')`.

---

## 3. Presets de cabecera (inamovibles)

Cada plantilla tiene **exactamente un** preset en `template_hero`. No editable por el cliente.

| Preset | Ratio crop | Bleed | Logo | Chef | Plantillas |
|--------|------------|-------|------|------|------------|
| `dark_bleed` | 16:9 | sí | rounded | sí | lumiere, otaku, japo, fastfood, asador, oriental |
| `compact_card` | 4:3 | no | rounded | sí | basic, pasion, temporada, pizza, mar, visual |
| `circle_emblem` | 16:9 | sí | circle | sí | elegance |
| `spotlight_dish` | 16:9 | no | rounded | no | bistro (fallback → `compact_card`) |
| `typographic_dark` | 16:9 | sí | rounded | no | nocturne |
| `minimal_bar` | 16:9 | no | rounded | no | catalogo |

Reglas de preset:

- Si `show_logo`: siempre bloque de marca (logo-chip + nombre; chef/slogan si aplica).
- `spotlight_dish` sin plato destacado → fallback automático a `compact_card`.
- Banner sin imagen → `asset('img/default-header.jpg')` + overlay por defecto.
- Sin logo → nombre tipográfico; nunca hueco vacío en zona de marca.

---

## 4. Tokens CSS obligatorios

Definidos en `theme-vars.blade.php`. **No duplicar** valores fijos en CSS por plantilla; extender tokens si hace falta.

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

---

## 5. Reglas de cabecera y logo

### Banner

- Análisis en subida: `header_luminance`, `header_overlay_mode`, `header_overlay_strength`, `header_crop`.
- Overlay **dinámico** vía clases `wn-menu-hero--overlay-{dark|light}` y `--wn-hero-overlay-strength`.
- **Prohibido**: gradientes fijos que ignoren luminancia del banner.
- Recorte guiado en admin (Cropper.js) con ratio del preset activo.
- `background-position` desde focal point del crop.

### Logo

- **Siempre** dentro de `logo-chip` (`--bg-light` | `--bg-dark` | `--bg-glass`).
- Tamaños: `--sm` (44px) en barra compacta; tamaño hero en cabecera principal.
- Variante calculada en servidor (`logo_chip_variant`); JS (`webnu-logo-autocontrast.js`) solo fallback.
- **Prohibido**: `<img class="wn-modern-header__logo">` u otro logo crudo sobre fondo variable.

### Texto en hero

- Tono explícito: `wn-menu-hero--text-light` o `wn-menu-hero--text-dark`.
- Título con `clamp()`; sombra de texto solo cuando `--wn-hero-text-tone: light`.
- Slogan máx. ~80 caracteres en admin; truncar visualmente con CSS si hace falta, no overflow horizontal.

---

## 6. Reglas de tarjetas de plato

| Elemento | Valor / regla |
|----------|----------------|
| Ratio imagen stacked | 16:10 |
| Ratio imagen horizontal | 4:3 (ancho ~120px) |
| Radio | `var(--wn-card-radius)` |
| Sombra | `var(--wn-card-shadow)` |
| Título | 1rem / 600 |
| Descripción | 0.875rem, máx. 2 líneas (`-webkit-line-clamp: 2`) |
| Touch targets | mínimo 44×44px (favorito, detalle, chevron) |
| Sin imagen | clase `wn-modern-card--no-media`; layout degradado elegante |
| Precio | siempre visible; no oculto por truncado de título |
| Badges | no tapar título ni precio |

Variantes specialty (mantener alineadas visualmente):

- `product-overlay` — nocturne (primer plato / destacados)
- `product-temporada` — temporada
- `product-catalogo` — catalogo

---

## 7. Navegación y layout móvil

- Nav sticky: chips horizontales con scroll; estado activo obvio.
- `--wn-scroll-offset` para que el título de sección no quede bajo nav al hacer scroll.
- Safe area: `max(1rem, env(safe-area-inset-*))` en márgenes laterales e inferiores.
- Viewports de prueba obligatorios: **375×667** y **390×844** (mínimo 320px ancho).
- **Prohibido**: scroll horizontal accidental.
- Un solo control de info de negocio (`#wn-info-toggle`); no duplicar en header y floating salvo preset que lo exija (`minimal_bar` vs hero).

---

## 8. Tema y color

Personalizable vía `theme_settings` + defaults en `config/company_templates.php` → `defaults.{template}`.

- Máximo **2 familias** tipográficas (`font_heading`, `font_body`).
- `text_muted` solo para secundario; precios y títulos usan `text` o `primary`/`accent` según plantilla.
- Modo oscuro: `surface` elevada sobre `bg`; bordes `rgba` sutiles.
- Modo claro: sombras suaves; evitar bordes negros duros.
- CSS por plantilla (`front-{template}.css`): **solo** overrides de color, tipografía, acentos decorativos — **nunca** estructura de hero, tarjetas o nav.

---

## 9. Accesibilidad

- `aria-label` en botones solo icono (info, favorito, detalle).
- `alt` en logos e imágenes de plato.
- Contraste mínimo **WCAG AA** (4.5:1) en texto normal sobre surface y hero.
- Estado activo/favorito: no depender solo del color (icono + `aria-pressed`).

---

## 10. Definition of Done (checklist QA)

Antes de dar por válida una plantilla (nueva o refactor):

- [ ] Logo legible con banner claro, oscuro y mixto (con chip correcto).
- [ ] Nombre del negocio legible en hero en las 3 condiciones anteriores.
- [ ] Carta usable **sin** banner (fallback default).
- [ ] Carta usable **sin** logo (solo nombre).
- [ ] Sección con 1 plato y con 15+ platos mantiene ritmo visual.
- [ ] Plato sin imagen no rompe layout.
- [ ] Precios largos y "Consultar" no desbordan en 320px.
- [ ] Nav sticky no tapa el primer plato al saltar a sección.
- [ ] Modal de detalle coherente con tarjeta.
- [ ] Sin scroll horizontal en 320px.
- [ ] Preview OK: `http://127.0.0.1:8000/carta/demo?tpl={template}`.
- [ ] No hay CSS estructural duplicado en `front-{template}.css`.
- [ ] Preset registrado en `template_hero` y documentado si es nuevo.

---

## 11. Prohibiciones explícitas

1. Layouts hero ad hoc fuera de `menu-hero.blade.php`.
2. Logo sin chip en cabecera o hero.
3. Overlays de banner fijos (misma opacidad para toda foto).
4. Estructura de tarjetas/nav reimplementada por plantilla.
5. Tamaños hero en px fijos sin `clamp()`.
6. Depender de que el cliente suba "la foto perfecta" para legibilidad.
7. Parámetro `heroMode` en blades raíz (obsoleto).
8. Modificar estructura del hero desde el admin (solo contenido visual).

---

## 12. Prompt estándar (agente / diseño / IA)

Copiar y usar tal cual al generar o revisar plantillas:

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

CABECERA
- Bloque marca cuando show_logo: logo-chip + nombre (+ chef/slogan).
- Overlay dinámico (--wn-hero-overlay-mode/strength); texto (--wn-hero-text-tone).
- Recorte banner según ratio preset (16:9 o 4:3); focal --wn-hero-focal-x/y.
- min-height con clamp(); spotlight_dish → fallback compact_card sin plato.

LOGO
- Chip sm (44px) en barra; md en hero. Fallback: nombre tipográfico.

TARJETAS
- Stacked 16:10; horizontal 4:3. Radio 16px. Touch ≥44px.
- Título 1rem/600; descripción 2 líneas max. Precio siempre visible.

NAV
- Sticky chips; --wn-scroll-offset correcto. Safe-area en márgenes.

CSS
- Estructura en front-menu-ui.css / front-menu-hero.css / front-modern.css.
- front-{template}.css SOLO acentos color/tipo.

DoD: pasar checklist sección 10 de docs/MENU-TEMPLATE-STANDARD.md.
Preview: /carta/demo?tpl={template} en móvil 375px y 320px.

PROHIBIDO: heroMode manual, logo sin chip, overlays fijos, CSS estructural por plantilla.
```

---

## 13. Prompt corto (revisión rápida)

```text
¿Cumple estándar Webnu premium?
□ logo-chip con contraste □ hero overlay por luminancia □ bloque marca completo
□ texto light/dark correcto □ tarjetas ratio/sombra/touch 44px
□ nav sin tapar contenido □ OK sin logo/banner/foto
□ solo tokens --wn-* y menu-hero □ preview /carta/demo?tpl=X en 320px
Si algún □ falla → no válido.
```

---

## 14. Mapa de las 16 plantillas

| Template | Preset | cardLayout típico | Nav | Tarjeta specialty |
|----------|--------|-------------------|-----|-------------------|
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
| atelier | typographic_dark | overlay (todos) | chips sticky | modern |
| maison | typographic_dark | stacked + overlay estrella | chips sticky | modern |

---

## 15. Verificación local

```bash
# Arrancar entorno
.\run-local.ps1

# Preview por plantilla
http://127.0.0.1:8000/carta/demo?tpl=lumiere
http://127.0.0.1:8000/carta/demo-cocktails   # nocturne curada
```

Tras cambios en banner de clientes existentes en producción:

```bash
php artisan migrate
php artisan webnu:headers:reanalyze
```

---

## 16. Añadir plantilla nueva (procedimiento)

1. Entrada en `config/company_templates.php` → `templates`, `defaults`, `template_hero`, `presets` si aplica.
2. Blade raíz mínimo (`{nombre}.blade.php`) → solo `modern-menu-layout`.
3. `front-{nombre}.css` → solo acentos (puede estar casi vacío).
4. SVG preview admin en `public/img/admin/templates/`.
5. Pasar checklist §10 y preview `?tpl={nombre}`.
6. No crear layout propio salvo tarjeta specialty justificada y alineada al §6.

---

*Última revisión: alineado con sistema menu-hero unificado y 16 plantillas QR en `main`.*
