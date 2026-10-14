# Galería plantillas: polish UX + i18n (2026-10-14)

## Problema
En Ambre/Swipe/Rail/Grid/Deck/Harbor: símbolo € corrupto (`�`), scrollbars visibles, iconos descentrados, alérgenos en EN con carta ES (Accept-Language del navegador en `/carta/demo`).

## Decisiones (aprobadas)
- Precio: helper único UTF-8 seguro (`7,50 €`).
- Carruseles: swipe/gesto + dots clicables; sin barras de scroll.
- Iconos: centrados con flex + caja fija SVG.
- Locale: `menuLocale` manda UI + alérgenos; demo sin `lang` → default compañía (ES).

## Alcance
Plantillas galería nuevas + partials compartidos + `MenuLocaleService` demo. Fuera: hub conectores.
