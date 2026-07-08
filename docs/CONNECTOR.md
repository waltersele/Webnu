# Content Connector — Webnu ↔ Sonartop

Webnu expone una API HTTP para que **Sonartop** publique y consulte artículos del blog multidioma (`es`, `en`, `fr`).

## Configuración

En `.env` de Webnu (y en Sonartop como `connector_secret`):

```env
CONTENT_CONNECTOR_SECRET=una-clave-larga-y-aleatoria
```

El valor debe ser **idéntico** en ambos sistemas.

## Autenticación (HMAC-SHA256)

Las rutas protegidas exigen la cabecera:

```
X-Connector-Signature: sha256=<hex>
```

Donde `<hex>` es:

```text
hash_hmac('sha256', <cuerpo_raw_de_la_petición>, CONTENT_CONNECTOR_SECRET)
```

- **GET** sin cuerpo: firmar la cadena vacía `""`.
- **POST** JSON: firmar el cuerpo **exacto** enviado (bytes raw), no un JSON reordenado.

Ejemplo en PHP:

```php
$body = json_encode($payload, JSON_UNESCAPED_UNICODE);
$signature = 'sha256=' . hash_hmac('sha256', $body, $secret);
```

## Endpoints

Base: `https://webnu.es/api/content-connector`

| Método | Ruta     | Firma | Descripción                          |
|--------|----------|-------|--------------------------------------|
| GET    | `/health`| No    | Comprueba que el conector responde   |
| GET    | `/posts` | Sí    | Lista publicaciones en todos los idiomas |
| POST   | `/posts` | Sí    | Crea o actualiza un artículo         |

### GET `/health`

Respuesta `200`:

```json
{ "status": "ok" }
```

### GET `/posts`

Respuesta `200`:

```json
{
  "posts": [
    {
      "slug": "bienvenida-webnu",
      "title": "Bienvenida a Webnu",
      "url": "https://webnu.es/es/blog/bienvenida-webnu",
      "excerpt": "Resumen del artículo…",
      "published_at": "2026-07-08T10:00:00+00:00",
      "locale": "es"
    }
  ]
}
```

### POST `/posts`

Cuerpo JSON:

| Campo     | Tipo   | Obligatorio | Descripción |
|-----------|--------|-------------|-------------|
| `title`   | string | Sí          | Título del artículo |
| `content` | string | Sí          | Cuerpo en **HTML** (se sanitiza) |
| `slug`    | string | Sí          | Slug URL (`a-z`, `0-9`, guiones) |
| `locale`  | string | Sí          | `es`, `en` o `fr` |
| `meta`    | object | No          | Metadatos opcionales |

Campos útiles en `meta`:

| Clave              | Uso |
|--------------------|-----|
| `group_id`         | Agrupa traducciones del mismo artículo |
| `post_id`          | Alias de `group_id` |
| `article_id`       | Alias de `group_id` |
| `excerpt`          | Resumen manual |
| `meta_title`       | Título SEO |
| `meta_description` | Descripción SEO |

Respuesta `201`:

```json
{
  "status": "published",
  "url": "https://webnu.es/es/blog/mi-articulo",
  "locale": "es"
}
```

El artículo queda **publicado** automáticamente al recibir el POST.

## Ejemplos curl

### Health (sin firma)

```bash
curl -s https://webnu.es/api/content-connector/health
```

### Listar posts

```bash
SECRET="tu-secreto"
SIG="sha256=$(printf '' | openssl dgst -sha256 -hmac "$SECRET" | awk '{print $2}')"
curl -s -H "X-Connector-Signature: $SIG" \
  https://webnu.es/api/content-connector/posts
```

### Publicar artículo

```bash
SECRET="tu-secreto"
BODY='{"title":"Hola Webnu","content":"<p>Primer post.</p>","slug":"hola-webnu","locale":"es","meta":{"group_id":"art-001"}}'
SIG="sha256=$(printf '%s' "$BODY" | openssl dgst -sha256 -hmac "$SECRET" | awk '{print $2}')"
curl -s -X POST https://webnu.es/api/content-connector/posts \
  -H "Content-Type: application/json" \
  -H "X-Connector-Signature: $SIG" \
  -d "$BODY"
```

## Errores

| Código | Motivo |
|--------|--------|
| `401`  | Firma ausente o incorrecta |
| `422`  | Validación (locale, slug, campos obligatorios) |
| `503`  | `CONTENT_CONNECTOR_SECRET` no configurado |

## URLs públicas del blog

- `/blog` → redirige al idioma del visitante (`es` por defecto).
- `/{locale}/blog` — listado (`es`, `en`, `fr`).
- `/{locale}/blog/{slug}` — artículo.

Sitemap: `https://webnu.es/sitemap.xml`

## Admin Webnu

Los artículos llegan principalmente por Sonartop. En **Admin → Plataforma → Blog** se pueden revisar, editar traducciones y cambiar borrador/publicado.
