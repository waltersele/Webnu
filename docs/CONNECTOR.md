# Content Connector — Webnu ↔ Sonartop

Webnu expone una API HTTP para que **Sonartop** publique y consulte artículos del blog multidioma (`es`, `en`, `fr`).

## Configuración

**Recomendado (producción):** Admin → **Plataforma → Configuración** → sección *Blog — Content Connector* → *Secreto HMAC*.

Alternativa en `.env` (desarrollo o fallback):

```env
CONTENT_CONNECTOR_SECRET=una-clave-larga-y-aleatoria
```

El valor debe ser **idéntico** al `connector_secret` configurado en Sonartop (cliente que envía los artículos).

## Autenticación (HMAC-SHA256)

Las rutas protegidas exigen la cabecera:

```
X-Connector-Signature: <hex64>
```

Donde `<hex64>` es el HMAC-SHA256 en **hexadecimal crudo** (64 caracteres), sin prefijo:

```text
hash_hmac('sha256', <cuerpo_raw_de_la_petición>, CONTENT_CONNECTOR_SECRET)
```

También se acepta el formato alternativo `sha256=<hex64>` (retrocompatibilidad).

- **GET** sin cuerpo: firmar la cadena vacía `""`.
- **POST** JSON: firmar el cuerpo **exacto** enviado (bytes raw), no un JSON re-parseado ni re-serializado.

Ejemplo en PHP (formato Sonartop):

```php
$body = json_encode($payload, JSON_UNESCAPED_UNICODE);
$signature = hash_hmac('sha256', $body, $secret);
```

## Endpoints

Base: `https://webnu.es/api/content-connector`

| Método | Ruta     | Firma | Descripción                          |
|--------|----------|-------|--------------------------------------|
| GET    | `/health`| No    | Comprueba que el conector responde   |
| GET    | `/posts` | Sí    | Lista publicaciones en todos los idiomas |
| POST   | `/posts` | Sí    | Crea un artículo                     |
| PUT    | `/posts/{id}` | Sí | Actualiza un artículo existente      |

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
      "id": "42",
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
  "id": "42",
  "url": "https://webnu.es/es/blog/mi-articulo"
}
```

El `id` es el identificador de la traducción en Webnu (`blog_post_translations.id`). Sonartop debe guardarlo para futuras ediciones vía PUT.

### PUT `/posts/{id}`

Actualiza el artículo existente (mismo body que POST). El `id` es el devuelto en el POST anterior.

Respuesta `200`:

```json
{
  "id": "42",
  "url": "https://webnu.es/es/blog/mi-articulo"
}
```

El artículo queda **publicado** automáticamente al recibir POST o PUT.

## Ejemplos curl

### Health (sin firma)

```bash
curl -s https://webnu.es/api/content-connector/health
```

### Listar posts

```bash
SECRET="tu-secreto"
SIG=$(printf '' | openssl dgst -sha256 -hmac "$SECRET" | awk '{print $2}')
curl -s -H "X-Connector-Signature: $SIG" \
  https://webnu.es/api/content-connector/posts
```

### Publicar artículo

```bash
SECRET="tu-secreto"
BODY='{"title":"Hola Webnu","content":"<p>Primer post.</p>","slug":"hola-webnu","locale":"es","meta":{"group_id":"art-001"}}'
SIG=$(printf '%s' "$BODY" | openssl dgst -sha256 -hmac "$SECRET" | awk '{print $2}')
curl -s -X POST https://webnu.es/api/content-connector/posts \
  -H "Content-Type: application/json" \
  -H "X-Connector-Signature: $SIG" \
  -d "$BODY"
```

Formato alternativo (también válido): `X-Connector-Signature: sha256=$SIG`

### Actualizar artículo

```bash
SECRET="tu-secreto"
BODY='{"title":"Título actualizado","content":"<p>Nuevo contenido.</p>","slug":"hola-webnu","locale":"es"}'
SIG=$(printf '%s' "$BODY" | openssl dgst -sha256 -hmac "$SECRET" | awk '{print $2}')
curl -s -X PUT https://webnu.es/api/content-connector/posts/42 \
  -H "Content-Type: application/json" \
  -H "X-Connector-Signature: $SIG" \
  -d "$BODY"
```

## Errores

| Código | Motivo |
|--------|--------|
| `401`  | Firma ausente o incorrecta |
| `422`  | Validación (locale, slug, campos obligatorios) |
| `503`  | Secreto no configurado (Admin → Plataforma → Configuración) |

## URLs públicas del blog

- `/blog` — hub canónico (siempre español, HTTP 200; recomendado para Sonartop).
- `/{locale}/blog` — listado por idioma (`es`, `en`, `fr`).
- `/{locale}/blog/{slug}` — artículo.

En Sonartop, usa **`https://webnu.es/blog`** como URL del blog. Los artículos en inglés o francés se publican con `locale: "en"` / `"fr"` en el connector y aparecen en `/en/blog` y `/fr/blog`.

Sitemap: `https://webnu.es/sitemap.xml`

## Admin Webnu

Los artículos llegan principalmente por Sonartop. En **Admin → Plataforma → Blog** se pueden revisar, editar traducciones y cambiar borrador/publicado.
