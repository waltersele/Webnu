# Worker de captura de menús (Riesgo 0)

Script **aislado** del monolito Webnu. Hace scraping, llama a Gemini y envía JSON limpio al servidor de producción. Si la IP es baneada o el proceso consume mucha RAM, **no afecta** a las cartas de clientes.

Ubicación: [`menu-capture-worker/`](../menu-capture-worker/) en la raíz del repositorio.

## Flujo

1. CSV con `restaurant_name,source_url,logo_url`
2. Playwright visita la URL (HTML o PDF)
3. Gemini estructura el menú (`sections[]`)
4. Validación JSON (AJV)
5. `POST /api/v1/demos/create` en Webnu (Pre-Alta staging)

## Instalación

```bash
cd menu-capture-worker
cp .env.example .env
# Editar GEMINI_API_KEY y WEBNU_DEMO_API_KEY (= PRE_ALTA_INGEST_KEY en Webnu)
npm install
```

## Uso

```bash
# Prueba sin enviar a Webnu
npm run capture -- --input examples/urls.csv --dry-run --limit 1

# Una fila real contra local
npm run capture -- --input urls.csv --limit 1

# Lote con concurrencia 1 (recomendado)
npm run capture -- --input urls.csv --concurrency 1 --batch-id 2026-05-24-office
```

Opciones: `--from-row`, `--limit`, `--dry-run`, `--batch-id`.

Logs: `menu-capture-worker/logs/capture-YYYY-MM-DD.jsonl`

## API Webnu (receptor pasivo)

```http
POST /api/v1/demos/create
X-Webnu-Demo-Key: {PRE_ALTA_INGEST_KEY}
Content-Type: application/json
```

Alias: `POST /api/pre-alta/ingest` con `X-Pre-Alta-Key`.

Respuesta `201`: `public_url`, `claim_url`, `expires_at`. Ver [PRE-ALTA.md](PRE-ALTA.md).

## Variables Webnu (.env producción)

```
PRE_ALTA_INGEST_KEY=clave-larga-aleatoria
```

El worker usa la misma clave en `WEBNU_DEMO_API_KEY`.

## Despliegue recomendado

- **Desarrollo:** ejecutar en tu PC durante la jornada comercial.
- **Producción:** VPS barato (Hetzner/DigitalOcean) **separado** del servidor Webnu; nunca instalar Playwright en el servidor de cartas.

## Troubleshooting

| Problema | Acción |
|----------|--------|
| `401` en Webnu | Revisar `WEBNU_DEMO_API_KEY` = `PRE_ALTA_INGEST_KEY` |
| `503` Ingesta no configurada | Definir `PRE_ALTA_INGEST_KEY` en el servidor |
| IP baneada | Cambiar IP del VPS o pausar; producción intacta |
| PDF vacío | La carta puede ser imagen escaneada; usar fotos en panel `menu-scan` |
