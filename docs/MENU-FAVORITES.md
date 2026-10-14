# Favoritos en la carta (comanda al camarero)

Lista de platos marcados por el comensal en la carta pública digital. Persistencia en **localStorage** del navegador (sin login ni API en v1).

## Flujo comensal

1. En la carta (`menu_type = 1`, plantillas modernas), cada producto muestra un botón corazón.
2. Un clic añade o quita el plato de la lista.
3. La barra inferior **Mis favoritos** muestra un badge con el total.
4. Al abrir el panel a pantalla completa, cada plato incluye:
   - Nombre en el **idioma del comensal** (`?lang=` / detección), con etiqueta del idioma.
   - Nombre en el **idioma base de la carta** (`company.default_locale`) para el camarero.
   - Foto y precio formateado.
5. Texto de ayuda bajo el título: *«Muestra esta pantalla al camarero…»* (sin botón extra).

## Almacenamiento

- Clave: `webnu_favs_{companyId}` → array ordenado de IDs de producto.
- Si un producto ya no existe en el catálogo embebido, se ignora al pintar la lista.
- Recargar la página mantiene la selección en el mismo dispositivo/navegador.

## Servidor

| Pieza | Rol |
|-------|-----|
| `MenuService::applyProductLocale()` | Atributos transitivos `name_locale` y `name_original` |
| `MenuFavoritesCatalog` | JSON embebido en `#webnu-favorites-catalog` |
| `PagesController::see_menu` | Pasa `favoritesEnabled` y `favoritesCatalog` a la vista |
| `companies.menu_favorites_enabled` | Toggle por carta (default `true`) |

## Admin

En **Personalizar** → **Lista de favoritos** (solo cartas digitales): activar/desactivar favoritos en la carta pública. Guardado vía `CompaniesController@update` con `studio_step=favorites`.

## Front

- Partial barra: `resources/views/themes/partials/menu-favorites.blade.php`
- Partial botón: `resources/views/themes/partials/menu-fav-button.blade.php`
- JS: `public/js/webnu-menu-favorites.js`
- Textos UI: `config/menu_locales.php` → `ui.{locale}.favorites_*`

### Roles de botón (obligatorio)

| Clase | Dónde | Look |
|-------|--------|------|
| `wn-fav-btn--action` | Footer/lista de card (donde iba el `+`) | Círculo con acento del tema |
| `wn-fav-btn--modal` + `wn-media-chrome` | Overlay del modal sobre foto/vídeo | **Misma pastilla negra semitransparente que la X** |

El chrome del modal (`wn-media-chrome`) está **bloqueado** en `theme-modal-overrides.blade.php` (carga después de `front-{template}.css`). Los temas **no** deben sobrescribirlo.

Si un tema estiliza `.wn-fav-btn`, debe usar `:not(.wn-media-chrome)` (ver `front-lumiere.css`, `front-velvet.css`, etc.).

## Alcance v1

- Cartas digitales modernas / galería (no PDF, no `menus-combined`).
- Acentos de fav en card: sí en `front-{template}.css`. Chrome sobre media: no.

## Verificación manual

1. `http://127.0.0.1:8000/carta/demo?lang=en`
2. Marcar 2 platos → badge = 2.
3. Panel: nombre EN + subtítulo ES + foto.
4. Recargar → lista persiste.
5. Desactivar en admin → desaparecen barra y botones corazón.

## Tests

```bash
php artisan test --filter=MenuFavorites
```
