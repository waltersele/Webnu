# Propuesta: 6 plantillas TV premium

**Estado:** implementadas en Webnu (Blade + CSS + registro en `config/tvpik_templates.php`).

Documento de producto para ampliar el catálogo TVPik/Webnu más allá de las 7 plantillas funcionales actuales. Animaciones: **básicas** (fade, Ken Burns, rotación temporal) — suficiente para sensación premium si el layout es fuerte.

**Datos disponibles sin cambios de API** (`TvMenuPresenter`): secciones, platos, destacados, vídeos, menú del día, menús activos, logo, cabecera, color de marca (`accent`), locale.

---

## Resumen comparativo

| # | Clave | Nombre | Para quién | Diferencia vs. actuales |
|---|-------|--------|------------|-------------------------|
| 1 | `cinema` | Cinema | Fine dining, hotel | Más editorial que `hero`; menos UI, más cine |
| 2 | `sommelier` | Sommelier | Bar de vinos, coctelería | Lista elegante, no carrusel ni rejilla |
| 3 | `degustacion` | Degustación | Menú cerrado / tasting | `daily` con narrativa vertical premium |
| 4 | `signature` | Firma del chef | Restaurante con estrella | Plato + descripción + sello de marca |
| 5 | `lounge` | Lounge | Bar de hotel, rooftop | Split ambiente + recomendaciones cortas |
| 6 | `marquee` | Marquee | Tapas, barra, sports bar | Hero arriba + ticker de platos abajo |

**Gating (implementado):** Plus y Franquicias → `features.tvpik_premium_templates: true`. Pro + add-on pantalla → solo 7 estándar. Publicación bloqueada en `TvpikPublishService`; preview siempre permitida. API: `GET /api/signage/tv-templates` devuelve `locked` por plantilla.

**Prioridad de implementación sugerida:** 1 → 4 → 6 → 3 → 5 → 2 (impacto visual / esfuerzo).

---

## 1. Cinema

**Clave:** `cinema` · **Categoría:** `restaurant` · **Rotación:** 14 s · **Header:** no

### Concepto
Un plato a pantalla completa, tratamiento **cinematográfico**: imagen a sangre, viñeta oscura, tipografía serif/display grande, precio discreto en esquina. Sensación de cartel de cine o carta de hotel 5★.

### Layout (16:9)
```
┌─────────────────────────────────────┐
│  [foto plato full bleed + Ken Burns]│
│                                     │
│  ENTRANTES                          │  ← categoría pequeña, tracking amplio
│  Vieiras con emulsión de azafrán    │  ← título 1 línea, clamp si largo
│                          24,50 €    │  ← precio, esquina inf. derecha
│  ● ○ ○                              │  ← dots si hay más slides
└─────────────────────────────────────┘
```

### Datos
- Slides: `$featured` con foto; fallback `$highlights` con foto.
- Categoría: `$product->section->name`.
- Precio: `TvMenuPresenter::formatPrice()`.

### Animación
- Carrusel `data-tv-carousel` (fade 900 ms).
- Ken Burns en imagen activa (scale 1 → 1.08, 14 s).
- Título: fade-in + translateY(12px) al activar slide.

### vs. `hero`
`hero` ya es full bleed, pero muestra topbar con logo y badge «Galería». **Cinema** elimina chrome, tipografía más refinada, ritmo más lento — menos «app», más «proyección».

### Esfuerzo: **M** (1 blade + CSS; reutiliza carrusel existente)

---

## 2. Sommelier

**Clave:** `sommelier` · **Categoría:** `restaurant` · **Rotación:** 18 s por sección · **Header:** sí (minimal)

### Concepto
Carta de **vinos, cócteles o digestivos**: pocas líneas, mucho aire. Dos columnas en pantallas anchas; una columna centrada en vertical. Ideal cuando cada ítem es caro y el nombre importa más que la foto.

### Layout
```
┌─────────────────────────────────────┐
│  Logo · Nombre local                │
│  ─────────────────────────────────  │
│  VINOS BLANCOS                      │
│  Albariño Rías Baixas      28,00 €  │
│  Verdejo Rueda             22,00 €  │
│  Chardonnay Reserva        34,00 €  │
│                                     │
│  (fade a siguiente sección)         │
└─────────────────────────────────────┘
```

### Datos
- `$sections` rotando (`data-tv-rotate`).
- Máx. 8 ítems por slide; si hay más, paginar dentro de la sección (sub-slides).
- Sin foto obligatoria; si el plato tiene imagen, miniatura circular opcional a la izquierda (40 px).

### Animación
- Rotación entre secciones con crossfade (mejorar `initRotate` con opacity, no solo display).
- Entrada de título de sección: fade-in 0.6 s.

### vs. `menu`
`menu` es lista densa multi-sección pensada para carta completa. **Sommelier** asume **pocos ítems por pantalla**, tipografía mayor, sin descripción.

### Esfuerzo: **M** (blade + CSS; pequeña mejora opcional en `webnu-tv.js` para fade en rotate)

---

## 3. Degustación

**Clave:** `degustacion` · **Categoría:** `restaurant` · **Rotación:** 20 s (si varios menús) · **Header:** no

### Concepto
Presentación del **menú del día / menú degustación** como experiencia narrativa: tres actos (entrante → principal → postre) en vertical, línea conectora, precio total destacado en dorado/marca.

### Layout
```
┌─────────────────────────────────────┐
│  [foto hero del menú, 45% altura]   │
│  MENÚ DEGUSTACIÓN                   │
│  ─── Entrante ───                   │
│  Crema de calabaza                  │
│  ─── Principal ───                  │
│  Lubina a la brasa                  │
│  ─── Postre ───                     │
│  Coulant de chocolate               │
│           Menú completo  38,00 €    │
└─────────────────────────────────────┘
```

### Datos
- Reutiliza lógica de `daily.blade.php`: `$menus`, `$activeMenu`, ítems por curso.
- Foto: `TvMenuPresenter::menuHeroImage($menu)`.
- Precio: `$menu->price` o campo equivalente del menú del día.

### Animación
- Fade entre menús si hay varios (`data-tv-rotate`).
- Línea vertical con «pulse» sutil en el conector (CSS).
- Precio total: scale-in al cargar slide.

### vs. `daily`
`daily` ya existe pero layout más funcional (grid, estilo carta). **Degustación** apuesta por **ritual y lujo**, menos densidad, más storytelling.

### Esfuerzo: **M–L** (fork de `daily` + CSS distinto; datos ya están)

---

## 4. Signature (Firma del chef)

**Clave:** `signature` · **Categoría:** `restaurant` · **Rotación:** 12 s · **Header:** no

### Concepto
Un **plato estrella con contexto**: foto grande (60%), bloque texto con nombre, descripción corta (2 líneas), precio, badge «Sugerencia del chef» si `highlight` está definido. Logo del local en marca de agua suave.

### Layout
```
┌──────────────────┬──────────────────┐
│                  │  ★ Sugerencia    │
│   [foto plato]   │  Rabo de toro    │
│                  │  estofado 12 h   │
│                  │  Con puré trufado│
│                  │  26,00 €         │
└──────────────────┴──────────────────┘
```

### Datos
- Slides: productos con `highlight` + descripción no vacía; fallback `$featured`.
- Badge según `Product::highlightMeta()` (Nuevo, Chef, etc.).

### Animación
- Carrusel con fade; foto con Ken Burns leve.
- Texto entra desde la derecha (translateX 24px → 0).

### vs. `featured`
`featured` es carrusel foto + caption genérico «Destacado». **Signature** exige **copy** (descripción), layout asimétrico 60/40, identidad de chef.

### Esfuerzo: **S–M** (variante de `featured` con grid distinto)

---

## 5. Lounge

**Clave:** `lounge` · **Categoría:** `restaurant` · **Rotación:** 10 s (solo fotos fondo) · **Header:** sí

### Concepto
**Ambiente + recomendaciones**: mitad izquierda foto de ambiente (cabecera del local o rotación suave de platos con foto); mitad derecha lista fija de 4–5 recomendaciones con precio. Estático en la lista; solo cambia el fondo.

### Layout
```
┌─────────────────┬───────────────────┐
│                 │  Recomendaciones  │
│  [background_   │  ● Gin Tonic   12€│
│   header o      │  ● Negroni     14€│
│   foto plato]   │  ● Olivas       6€│
│                 │  ● Croquetas   9€ │
└─────────────────┴───────────────────┘
```

### Datos
- Fondo: `$headerUrl` fijo; si no hay, carrusel lento de `$highlights` (solo imagen).
- Lista: top 5 `$featured` o `$highlights` con precio.

### Animación
- Fondo: crossfade muy lento (10 s) si hay múltiples fotos.
- Lista: sin animación (legibilidad); opcional highlight sutil en fila cada 4 s.

### vs. `spotlight` / `tapas`
No compite con especial del día ni rejilla 2×2. **Lounge** es **dual**: atmósfera + carta corta de bar.

### Esfuerzo: **M** (layout split; dos fuentes de imagen)

---

## 6. Marquee

**Clave:** `marquee` · **Categoría:** `restaurant` · **Rotación:** 8 s (hero) · **Header:** no

### Concepto
**Impacto visual arriba, información continua abajo**: 70% pantalla = plato hero rotando; 30% = franja con **ticker horizontal** infinito de nombres + precios de todos los destacados (o sección «Barra»).

### Layout
```
┌─────────────────────────────────────┐
│                                     │
│     [plato hero rotando, fade]      │
│                                     │
├─────────────────────────────────────┤
│ ◀ Jamón ibérico 18€ · Croquetas 9€ │  ← scroll CSS infinito
│   Pulpo a la gallega 22€ · ...      │
└─────────────────────────────────────┘
```

### Datos
- Hero: `$featured` con foto (carrusel).
- Ticker: concat de `$highlights` o productos de una sección configurable vía query `?section=barra` (fase 2); v1 = todos los destacados.

### Animación
- Hero: carrusel existente.
- Ticker: `@keyframes` translateX loop (CSS puro, sin JS).
- Pausa ticker al cambiar slide hero (opcional, fase 2).

### vs. `tapas` / `hero`
Combina **impacto** de hero con **información continua** sin esperar rotación. Muy legible en barra con clientes de pie.

### Esfuerzo: **M** (CSS marquee + carrusel hero; patrón JS nuevo mínimo)

---

## Implementación técnica (común a las 6)

Por plantilla:

1. Entrada en `config/tvpik_templates.php` (`key`, `layout`, `view`, `category`, `rotate_seconds`, `show_header`, `thumbnail`, flag opcional `premium => true`).
2. Blade `resources/views/tv/templates/{layout}.blade.php`.
3. CSS bajo bloque `.wn-tv--{layout}` en `public/css/webnu-tv.css`.
4. Miniatura PNG/WebP real (captura con carta demo), no solo SVG genérico.
5. Exponer en `GET /api/signage/tv-templates` (ya existe catálogo) con campo `premium`.
6. Cumplir nivel **A** de integración tema ([`TV-TEMPLATE-STANDARD.md`](TV-TEMPLATE-STANDARD.md) §5).

### Mejoras transversales recomendadas (una vez, benefician a todas)

| Mejora | Beneficio |
|--------|-----------|
| Crossfade en `initRotate()` | Sommelier, Degustación se sienten más premium |
| Campo `premium` + badge en admin TVPik | Comunicación comercial clara |
| Capturas demo por plantilla | Vender la diferencia en onboarding |
| Query `?section=slug` en presenter | Marquee/Lounge filtrados por sección |

---

## Roadmap sugerido (3 entregas)

### Entrega A — «Wow inmediato» (2 semanas)
- **Cinema** + **Signature** + miniaturas reales de las 2.

### Entrega B — «Barra y menú cerrado» (2 semanas)
- **Marquee** + **Degustación**.

### Entrega C — «Carta de autor» (2 semanas)
- **Lounge** + **Sommelier** + crossfade en rotate.

---

## Métricas de éxito

- % pantallas publicadas con plantilla premium vs. básica.
- Tiempo medio en preview antes de publicar (engagement admin).
- Feedback cualitativo de clientes Plus (¿la TV «se ve de otro nivel»?).
- Reducción de pantallas que usan solo `menu` (hoy el default genérico).

---

## Fuera de alcance (fase posterior)

- Animaciones Lottie / After Effects embebidas.
- Plantillas retail (`events`, `retail` en categorías) — otro paquete de 6.
- Editor visual de plantillas por el restaurante.
- Sonido en plantillas TV.
