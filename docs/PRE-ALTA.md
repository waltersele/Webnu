# Pre-Alta — captura externa y reclamo

Módulo aislado de **staging** para cartas captadas fuera de Webnu. No escribe en `users`, `companies`, `sections` ni `products` hasta el flujo de **reclamo**.

## Ingesta (API)

Endpoint canónico para el **worker externo** (Riesgo 0):

```http
POST /api/v1/demos/create
X-Pre-Alta-Key: {PRE_ALTA_INGEST_KEY}
# o alias: X-Webnu-Demo-Key
Content-Type: application/json
```

Alias legacy (mismo handler y contrato):

```http
POST /api/pre-alta/ingest
X-Pre-Alta-Key: {PRE_ALTA_INGEST_KEY}
Content-Type: application/json
```

Ver también [MENU-CAPTURE-WORKER.md](MENU-CAPTURE-WORKER.md) para el script aislado que hace scraping + IA y llama a esta API.

### Body (ejemplo)

```json
{
  "restaurant_name": "Bar La Plaza",
  "logo_url": "https://cdn.ejemplo.com/logo.jpg",
  "sections": [
    {
      "name": "Entrantes",
      "products": [
        {
          "name": "Gazpacho",
          "description": "Tomate y aceite",
          "price_unit": "7,50",
          "image_url": "https://cdn.ejemplo.com/gazpacho.jpg",
          "allergens": ["Gluten"]
        }
      ]
    }
  ],
  "source_meta": { "capture_id": "ext-123" }
}
```

Mismo formato de secciones/platos que el escaneo IA (`MenuScanResult`).

### Respuesta `201`

```json
{
  "id": 1,
  "public_url": "https://tu-dominio/pre-alta/pa-xxxxxxxxxxxx",
  "claim_url": "https://tu-dominio/activar/{token-secreto-64-hex}",
  "expires_at": "2026-06-13T12:00:00+00:00"
}
```

El `claim_url` solo se devuelve **una vez**. Guárdalo en el sistema de captura.

## Vista pública

- `GET /pre-alta/{slug}` — preview de la carta (solo registros `pending` no caducados).
- Imágenes: `GET /pre-alta/media/{id}?path=...` (path debe estar en `media_manifest`).

## Reclamo

1. El restaurante abre `claim_url`.
2. Define nombre, email y contraseña.
3. El sistema crea usuario (trial 30 días), company, importa menú, mueve imágenes a `public/img/`, elimina staging y entra al panel.

## Almacenamiento temporal

- Tabla: `menu_pre_registrations`
- Disco: `storage/app/pre-alta/{id}/`
- Caducidad: `PRE_ALTA_RETENTION_DAYS` (default 20)

## Comandos programados

| Comando | Horario | Función |
|---------|---------|---------|
| `webnu:expire-trials` | 02:00 | Trial caducado → `plan=free` (sin borrar datos) |
| `webnu:purge-stale-pre-alta` | 03:00 | Purga leads no reclamados caducados |

Cron del servidor:

```bash
* * * * * cd /ruta/webnu && php artisan schedule:run >> /dev/null 2>&1
```

Opciones útiles:

```bash
php artisan webnu:expire-trials --dry-run
php artisan webnu:purge-stale-pre-alta --dry-run --limit=50
```

## Variables `.env`

Ver `.env.example`: `PRE_ALTA_INGEST_KEY`, `PRE_ALTA_RETENTION_DAYS`, límites de menú e hosts de imagen opcionales.
