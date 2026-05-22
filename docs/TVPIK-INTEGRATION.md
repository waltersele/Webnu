# Integración Webnu ↔ TVPik

Webnu es la **fuente de verdad** de la carta (secciones, platos, especial del día, vídeos). TVPik **reproduce** en pantallas URLs optimizadas para TV generadas por Webnu.

**Documentación API para el workspace TVPik (conexión, pantallas, plantillas, menús):** **[TVPIK-WORKSPACE-API.md](TVPIK-WORKSPACE-API.md)** — referencia completa para `tvpik-api` / `tvpik-web`.

**Facturación:** Webnu cobra todos los planes (incluido TVPik). TVPik solo consulta permisos — ver **[PLATFORM-BILLING-TVPIK.md](PLATFORM-BILLING-TVPIK.md)**.

## Flujo del restaurante (hub en Webnu)

1. Plan **Ilimitado** → menú **TV / TVPik** en el panel.
2. **Conectar TVPik** — pegar token de la app TVPik (Integraciones → Webnu).
3. En **Mis pantallas**, por cada TV: elegir carta, plantilla TV y **Publicar**.
4. Editar platos en **Mi carta** → republicación automática en pantallas vinculadas (cola de jobs).

## URLs de reproducción (TV)

| Plantilla | Ruta | Uso |
|-----------|------|-----|
| Carta completa | `/tv/{slug}/menu` | Secciones y precios, rotación |
| Plato del día | `/tv/{slug}/spotlight` | `daily_spotlight` + destacados |
| Destacados | `/tv/{slug}/featured` | Carrusel platos `highlight` |
| Vídeos | `/tv/{slug}/video` | Platos con vídeo corto |
| **Modo reproductor** | `/tv/{slug}/{layout}?player=1` | Pantalla completa para HDMI / Cast; sincroniza con Webnu |

La carta móvil/QR sigue en `/carta/{slug}` (sin cambios).

**Modo reproductor (HDMI / pantalla compartida):** ver **[TV-PLAYER-MODE.md](TV-PLAYER-MODE.md)**. La TV muestra la URL; tú editas en Mi carta y la pantalla recarga al cambiar `sync_version` (`GET /tv/{slug}/sync.json`).

## API de contenido (TVPik / terceros)

Base: `https://webnu.es/api/signage`  
Cabecera opcional: `X-Digital-Signage-Key`  
Auth: `Authorization: Bearer {api_token}`

| Método | Ruta | Uso |
|--------|------|-----|
| POST | `/login` | Token + `entitlements` |
| GET | `/account` | Permisos actualizados (plan, `features.tvpik`) |
| GET | `/me` | Alias de `/account` |
| GET | `/menus` | Listado de cartas |
| GET | `/menus/{slug}` | Contenido carta + `tv_urls` |
| GET | `/menus/{slug}/version` | Solo `sync_version` |

El menú incluye `sections`, `daily_spotlight`, `highlights`, `tv_urls` y `sync_version` (cabecera `X-Sync-Version`, respuesta 304 si no cambió).

**TVPik debe leer `entitlements.features.tvpik` antes de publicar pantallas** — detalle en [PLATFORM-BILLING-TVPIK.md](PLATFORM-BILLING-TVPIK.md).

## Configuración Webnu (`.env`)

```env
DIGITAL_SIGNAGE_APP_KEY=clave_compartida
TVPIK_API_URL=https://api.tvpik.es
TVPIK_APP_KEY=clave_compartida
TVPIK_WEB_URL=https://tvpik.es
TVPIK_STUB_SCREENS=false
```

Si `TVPIK_API_URL` está vacía, el panel muestra pantallas de demostración y guarda URLs localmente (útil en desarrollo).

## Contrato API TVPik (implementar en tvpik-api)

Webnu llama a estos endpoints con cabeceras:

- `Authorization: Bearer {tvpik_user_token}`
- `X-Webnu-Token: {webnu_api_token}`
- `X-Digital-Signage-Key` / `X-Webnu-App-Key`

| Método | Ruta | Body |
|--------|------|------|
| POST | `/integrations/webnu/connect` | `tvpik_token`, `webnu_token` |
| GET | `/integrations/webnu/screens` | — |
| POST | `/integrations/webnu/publish` | `screen_id`, `company_slug`, `template_key`, `publish_url`, `webnu_api_token` |

`publish_url` debe ser la URL `/tv/{slug}/{layout}`, no solo `/carta/{slug}`.

Respuesta `screens[]` sugerida:

```json
{
  "screens": [
    { "id": "abc", "name": "Barra", "online": true, "gallery_id": "12" }
  ]
}
```

## Base de datos Webnu

- `users.tvpik_api_token` (cifrado), `tvpik_connected_at`, `tvpik_org_id`
- `tvpik_screen_links` — vínculo pantalla ↔ negocio ↔ plantilla

Migración: `2026_05_21_160000_add_tvpik_integration_tables.php`

## Vídeos ligeros (Smart TV)

Al subir un vídeo de plato, Webnu intenta re-codificarlo con **FFmpeg** a H.264 baseline 720p, sin audio y `faststart` (menos peso, arranque rápido en TV flojas).

Variables opcionales en `.env`:

```env
PRODUCT_MAX_VIDEO_SECONDS=20
PRODUCT_MAX_VIDEO_KB=15360
PRODUCT_FFMPEG_ENABLED=true
PRODUCT_FFMPEG_PATH=ffmpeg
PRODUCT_TV_MAX_HEIGHT=720
PRODUCT_TV_STRIP_AUDIO=true
```

Sin FFmpeg se conserva el archivo original (respetando el límite de tamaño en la subida).

## Comandos

```bash
php artisan migrate
php artisan queue:work   # si usas cola para auto-sync
```

## Desarrollo local

```bash
# Pantallas demo sin API TVPik
TVPIK_STUB_SCREENS=true

php artisan serve
# Vista TV: http://127.0.0.1:8000/tv/demo/spotlight?preview=1
```

## Plantillas TV (vistas Blade)

| Clave | Vista | Carpeta |
|-------|-------|---------|
| `menu` | `tv.templates.menu` | `resources/views/tv/templates/menu.blade.php` |
| `spotlight` | `tv.templates.spotlight` | `resources/views/tv/templates/spotlight.blade.php` |
| `featured` | `tv.templates.featured` | `resources/views/tv/templates/featured.blade.php` |
| `video` | `tv.templates.video` | `resources/views/tv/templates/video.blade.php` |

Estilos: `public/css/webnu-tv.css` · Scripts: `public/js/webnu-tv.js` · Registro: `App\Services\Tv\TvTemplateRegistry`.

## Referencias en código

- [`app/Http/Controllers/TvMenuController.php`](../app/Http/Controllers/TvMenuController.php)
- [`app/Services/Tv/TvTemplateRegistry.php`](../app/Services/Tv/TvTemplateRegistry.php)
- [`app/Http/Controllers/Admin/TvpikController.php`](../app/Http/Controllers/Admin/TvpikController.php)
- [`app/Services/Tvpik/TvpikPublishService.php`](../app/Services/Tvpik/TvpikPublishService.php)
- [`config/tvpik_templates.php`](../config/tvpik_templates.php)
