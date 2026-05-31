# Sistema de cabecera móvil (Menu Hero)

Documentación del sistema unificado de banner, logo y contraste para las 15 plantillas QR.

**Estándar completo (condiciones inamovibles):** [MENU-TEMPLATE-STANDARD.md](MENU-TEMPLATE-STANDARD.md)

## Presets bloqueados por plantilla

Cada plantilla tiene un preset fijo en `config/company_templates.php` (`template_hero` + `hero_presets`). El cliente **no puede cambiar la estructura** del hero; solo colores, fuentes, logo y banner.

| Preset | Plantillas |
|--------|------------|
| `dark_bleed` | lumiere, otaku, japo, fastfood, asador |
| `compact_card` | pasion, temporada, pizza |
| `circle_emblem` | elegance, saffron, mar |
| `typographic_dark` | nocturne, maison |
| `minimal_bar` | catalogo |

## Metadatos de banner

Columnas en `companies`:

- `header_luminance`
- `header_overlay_mode` (`dark` | `light`)
- `header_overlay_strength` (0.45–0.92)
- `header_dominant_hex`
- `header_crop` (JSON `{x,y,w,h}` normalizado 0–1)

Se calculan en:

- `POST /admin/companies/{id}/header` (`CompaniesController@storeheader`)
- `PATCH /admin/companies/{id}/header-crop`
- `php artisan webnu:headers:reanalyze`

Servicio: `App\Services\BannerImageAnalyzer`.

## Vistas

- `resources/views/themes/partials/menu-hero.blade.php` — render unificado
- `resources/views/themes/partials/logo-chip.blade.php` — logo con contraste
- `resources/views/themes/partials/modern-menu-layout.blade.php` — shell compartido
- CSS: `public/css/themes/front-menu-hero.css`

Variables CSS inyectadas en `theme-vars.blade.php`:

- `--wn-hero-overlay-strength`
- `--wn-hero-overlay-mode`
- `--wn-hero-text-tone`
- `--wn-hero-focal-x` / `--wn-hero-focal-y`

## Admin: recorte guiado

Tras subir banner en **Identidad del negocio** (estudio `/admin/companies/{id}/edit`):

1. Se abre modal con Cropper.js
2. Ratio según plantilla activa (`heroRatios` en `WebnuCompanyStudio`)
3. Guardar → `PATCH header-crop` → refresco de preview

Assets: `public/materio/vendor/cropperjs/`

## Despliegue

```bash
php artisan migrate
php artisan webnu:headers:reanalyze
```

## Tests

- `tests/Unit/BannerImageAnalyzerTest.php`
- `tests/Feature/MenuHeroSystemTest.php`
