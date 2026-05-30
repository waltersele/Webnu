# Integración Webnu ↔ TVPik

Webnu es la **fuente de verdad** de la carta (secciones, platos, especial del día, vídeos). TVPik **reproduce** en pantallas URLs optimizadas para TV generadas por Webnu.

**Documentación API para el workspace TVPik (conexión, pantallas, plantillas, menús):** **[TVPIK-WORKSPACE-API.md](TVPIK-WORKSPACE-API.md)** — referencia completa para `tvpik-api` / `tvpik-web`.

**Facturación:** Webnu cobra todos los planes (incluido TVPik). TVPik solo consulta permisos — ver **[PLATFORM-BILLING-TVPIK.md](PLATFORM-BILLING-TVPIK.md)**.

## Dos flujos de conexión (coexisten)

| Dirección | Quién inicia | Rutas | Uso |
|-----------|--------------|-------|-----|
| **Webnu → TVPik** | Panel Webnu | `POST /admin/tvpik/connect`, `TVPIK_API_URL` | Pegar token TVPik, publicar pantallas desde `/admin/tvpik` |
| **TVPik → Webnu** | Workspace TVPik | `GET/POST /integrations/tvpik/connect` | OAuth: login Webnu y devolver `code` (= `api_token`) al callback de TVPik |

El flujo OAuth **no sustituye** el hub en Webnu; solo permite conectar desde la app TVPik.

## Flujo del restaurante (hub en Webnu)

1. Plan **Ilimitado** → menú **TV / TVPik** en el panel.
2. **Conectar TVPik** — pegar token de la app TVPik (Integraciones → Webnu).
3. En **Mis pantallas**, por cada TV: elegir carta, plantilla TV y **Publicar**.
4. Editar platos en **Mi carta** → republicación automática en pantallas vinculadas (cola de jobs).

## OAuth outbound (TVPik → Webnu)

TVPik redirige al usuario a:

```
GET {WEBNU_BASE}/integrations/tvpik/connect?state=...&redirect_uri={TVPIK_CALLBACK}
```

1. Si ya hay sesión Webnu → redirect inmediato al `redirect_uri` con `code` y `state`.
2. Si no → formulario email/contraseña (`POST /integrations/tvpik/connect`).
3. `code` = `users.api_token` (mismo token que `POST /api/signage/login`).
4. TVPik callback: `GET /api/v1/integrations/webnu/callback?code=...&state=...` → `GET /api/signage/account` con Bearer.

Controlador: [`app/Http/Controllers/Integrations/TvpikConnectController.php`](../app/Http/Controllers/Integrations/TvpikConnectController.php).  
Paquete de referencia: `c:\Users\Walter\tvpik\integrations\webnu-laravel\`.

**Usuarios solo Google:** sin contraseña local no pueden usar el formulario OAuth; si ya tienen sesión abierta en Webnu, `GET` autoriza sin formulario.

**Allowlist** (`TVPIK_ALLOWED_REDIRECT_URIS`, URIs exactas, separadas por coma). En `local` sin lista configurada se aceptan URIs que contengan `/integrations/webnu/callback`.

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
TVPIK_ALLOWED_REDIRECT_URIS=http://127.0.0.1:8001/api/v1/integrations/webnu/callback,http://localhost:8001/api/v1/integrations/webnu/callback
TVPIK_API_URL=https://api.tvpik.es
TVPIK_APP_KEY=clave_compartida
TVPIK_WEB_URL=https://tvpik.es
TVPIK_STUB_SCREENS=false
```

`DIGITAL_SIGNAGE_APP_KEY` debe coincidir con `WEBNU_DIGITAL_SIGNAGE_APP_KEY` (o `WEBNU_APP_KEY`) en `tvpik-api`.

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

**Webnu** (puerto 8000):

```bash
.\run-local.ps1
# o: php artisan serve --host=127.0.0.1 --port=8000
```

**TVPik API** (puerto 8001, reiniciar tras cambiar env):

```env
WEBNU_BASE_URL=http://127.0.0.1:8000
WEBNU_REDIRECT_URI=http://127.0.0.1:8001/api/v1/integrations/webnu/callback
WEBNU_DIGITAL_SIGNAGE_APP_KEY=dev-signage-key-compartida
```

**Webnu `.env`:**

```env
DIGITAL_SIGNAGE_APP_KEY=dev-signage-key-compartida
TVPIK_ALLOWED_REDIRECT_URIS=http://127.0.0.1:8001/api/v1/integrations/webnu/callback,http://localhost:8001/api/v1/integrations/webnu/callback
TVPIK_API_URL=http://127.0.0.1:8001
```

Comprobar OAuth:

```text
GET http://127.0.0.1:8000/integrations/tvpik/connect?state=test&redirect_uri=http%3A%2F%2F127.0.0.1%3A8001%2Fapi%2Fv1%2Fintegrations%2Fwebnu%2Fcallback
→ 200 (formulario), no 404
```

```bash
# Pantallas demo sin API TVPik (solo hub Webnu)
TVPIK_STUB_SCREENS=true

# Vista TV: http://127.0.0.1:8000/tv/demo/spotlight?preview=1
```

## Plantillas TV (vistas Blade)

| Clave | Vista | Tipo |
|-------|-------|------|
| `menu` | `tv.templates.menu` | Estándar |
| `spotlight` | `tv.templates.spotlight` | Estándar |
| `featured` | `tv.templates.featured` | Estándar |
| `video` | `tv.templates.video` | Estándar |
| `daily` | `tv.templates.daily` | Estándar |
| `hero` | `tv.templates.hero` | Estándar |
| `tapas` | `tv.templates.tapas` | Estándar |
| `cinema` | `tv.templates.cinema` | **Premium** |
| `sommelier` | `tv.templates.sommelier` | **Premium** |
| `degustacion` | `tv.templates.degustacion` | **Premium** |
| `signature` | `tv.templates.signature` | **Premium** |
| `lounge` | `tv.templates.lounge` | **Premium** |
| `marquee` | `tv.templates.marquee` | **Premium** |

Detalle de las premium: [`docs/TV-TEMPLATES-PREMIUM-PROPOSAL.md`](TV-TEMPLATES-PREMIUM-PROPOSAL.md).

Estilos: `public/css/webnu-tv.css` · Scripts: `public/js/webnu-tv.js` · Registro: `App\Services\Tv\TvTemplateRegistry`.

## Referencias en código

- [`app/Http/Controllers/Integrations/TvpikConnectController.php`](../app/Http/Controllers/Integrations/TvpikConnectController.php) — OAuth TVPik → Webnu
- [`app/Http/Controllers/TvMenuController.php`](../app/Http/Controllers/TvMenuController.php)
- [`app/Services/Tv/TvTemplateRegistry.php`](../app/Services/Tv/TvTemplateRegistry.php)
- [`app/Http/Controllers/Admin/TvpikController.php`](../app/Http/Controllers/Admin/TvpikController.php)
- [`app/Services/Tvpik/TvpikPublishService.php`](../app/Services/Tvpik/TvpikPublishService.php)
- [`config/tvpik_templates.php`](../config/tvpik_templates.php)
