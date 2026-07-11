# Content Connector — Webnu ↔ Sonartop

API HTTP para que **Sonartop** publique, actualice y sincronice artículos del blog multidioma.

## Configuración

**Producción:** Admin → **Plataforma → Configuración** → *Blog — Content Connector* → *Secreto HMAC*.

**Desarrollo:** `.env`

```env
CONTENT_CONNECTOR_SECRET=una-clave-larga-y-aleatoria
```

Debe coincidir con el *Secreto compartido* en Sonartop → Ajustes del proyecto → Conexiones.

## Autenticación (HMAC-SHA256)

Header:

```
X-Connector-Signature: <hex64>
```

- **Hex crudo** (64 caracteres minúsculas). Sonartop **no** usa prefijo `sha256=`.
- Firmar los **bytes crudos** del body (`php://input` / `$request->getContent()`).
- GET sin body: firmar cadena vacía `""`.
- Comparación en tiempo constante (`hash_equals`).

Ejemplo verificado:

```
secreto: mi-secreto-compartido
body:    {"title":"Hola mundo","locale":"es"}
firma:   e016a6376c8235b2529074b8f346e13d328475abd309d44447aa36c73170ad16
```

## Endpoints

Base: `https://webnu.es/api/content-connector`

| Método | Ruta | Firma | Descripción |
|--------|------|-------|-------------|
| GET | `/health` | No | Health check |
| GET | `/posts` | Sí | Lista posts para sync |
| GET | `/categories` | Sí | Categorías del blog |
| POST | `/posts` | Sí | Crear artículo |
| PUT | `/posts/{id}` | Sí | Actualizar artículo |

### GET `/health`

```json
{ "status": "ok" }
```

### GET `/posts`

Devuelve todos los posts con traducción (published, scheduled, draft):

```json
{
  "posts": [
    {
      "id": "42",
      "slug": "mi-articulo",
      "title": "Título",
      "url": "https://webnu.es/es/blog/mi-articulo",
      "excerpt": "Resumen",
      "published_at": "2026-07-10T09:00:00+00:00",
      "locale": "es",
      "category_id": "3",
      "status": "published"
    }
  ]
}
```

### GET `/categories`

Sonartop consulta esto antes de publicar para obtener IDs válidos.

```json
{
  "categories": [
    { "id": "1", "name": "Cartas digitales" }
  ]
}
```

### POST / PUT `/posts`

#### Campos obligatorios

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `title` | string | Título |
| `content` | string | HTML del artículo (sanitizado) |
| `slug` | string | Slug URL |
| `locale` | string | `es`, `en`, `fr`, `de`, `it`, `pt`, `ca` |
| `status` | string | `published` o `scheduled` |
| `published_at` | string | ISO 8601 |

#### Campos opcionales (root del JSON)

| Campo | Descripción |
|-------|-------------|
| `excerpt` | Resumen (prioridad sobre `meta.excerpt`) |
| `meta_title` | Título SEO |
| `meta_description` | Meta description |
| `focus_keyword` | Keyword principal |
| `category_id` | ID de `GET /categories` cuando la IA elige categoría |
| `faq_schema` | JSON-LD FAQPage — se renderiza en `<head>`, **no** en `content` |
| `featured_image_url` | URL de imagen destacada |
| `featured_image_alt` | Texto alternativo |
| `featured_image_base64` + `featured_image_mime` | Imagen en base64 si no hay URL |
| `group_id` / `article_id` / `post_id` | Agrupa traducciones multidioma |
| `meta` | Objeto legacy (excerpt, group_id, etc.) |

#### Respuesta POST (201) / PUT (200)

```json
{
  "id": "42",
  "url": "https://webnu.es/es/blog/mi-articulo"
}
```

El `id` es `blog_post_translations.id`. Sonartop debe guardarlo como `connector_article_id` para futuros PUT.

#### `faq_schema`

Enviar aparte del `content`. Webnu lo inserta como:

```html
<script type="application/ld+json">…</script>
```

en el `<head>` del artículo. Si va dentro de `content`, el sanitizador elimina `<script>` y el JSON queda visible.

#### Publicación programada

- `status: "scheduled"` o `published_at` futuro → post no visible hasta la fecha.
- Cron: `php artisan blog:publish-scheduled` (cada 5 min en schedule).

## Multidioma

Un POST/PUT por idioma con `locale` distinto. Agrupar traducciones con el mismo `group_id`.

## URLs públicas

- `/blog` — hub canónico (español)
- `/{locale}/blog` — listado
- `/{locale}/blog/{slug}` — artículo

## Checklist Sonartop

- [ ] GET `/health` → 200
- [ ] GET `/posts` → 200 con `id` en cada post
- [ ] GET `/categories` → 200
- [ ] POST → 201 `{ id, url }` con firma hex cruda
- [ ] PUT `/posts/{id}` → 200
- [ ] `category_id` asignado cuando viene
- [ ] `faq_schema` renderizado en head
- [ ] Cada locale en su ruta sin sobrescribir otros

## Test E2E local

```bash
export CONTENT_CONNECTOR_SECRET=test-connector-secret
php artisan serve --port=8765
php scripts/test-connector-e2e.php http://127.0.0.1:8765
```

## Errores

| Código | Motivo |
|--------|--------|
| 401 | Firma ausente o incorrecta |
| 422 | Validación |
| 503 | Secreto no configurado |
