# Escaneo de carta (Gemini + OCR)

Importa secciones y platos desde fotos o PDF usando Google Gemini. Si la API falla, el sistema puede usar **Tesseract OCR** como respaldo.

## Configuración

### Superadmin (recomendado)

1. Entra como superadmin → **Plataforma** → **Escaneo IA**.
2. Pega la API key de [Google AI Studio](https://aistudio.google.com/apikey) y guarda.
3. La clave se almacena cifrada en la base de datos.

### Respaldo en `.env`

```env
GEMINI_API_KEY=tu_clave
GEMINI_MODEL=gemini-2.5-flash-lite
```

### Uso en el restaurante

**Mi carta** → **Importar desde foto o PDF** → **Hacer foto** (cámara del móvil) o galería/PDF.

## Tesseract (fallback OCR)

### Windows

1. Descarga el instalador desde [UB Mannheim Tesseract](https://github.com/UB-Mannheim/tesseract/wiki).
2. Instala el paquete de idioma **Spanish** (`spa`).
3. Añade la carpeta de instalación al `PATH` (p. ej. `C:\Program Files\Tesseract-OCR`).
4. Opcional en `.env`:

```env
TESSERACT_BINARY=tesseract
TESSERACT_LANG=spa
```

Comprueba en PowerShell: `tesseract --version`

### Linux (servidor)

```bash
sudo apt install tesseract-ocr tesseract-ocr-spa
```

### macOS

```bash
brew install tesseract tesseract-lang
```

### PDF con OCR

Para PDF multipágina en fallback se usa **Imagick** (extensión PHP `imagick`) si está disponible. Sin Imagick, sube fotos en lugar de PDF cuando uses solo OCR.

## Límites

- Máx. 10 archivos por escaneo
- 8 MB por archivo (ajusta `upload_max_filesize` en `php-local.ini` si hace falta)
- **Plan Gratis:** 5 escaneos IA por cuenta (vitalicios, ver `UserPlanService` y [ONBOARDING-FREEMIUM.md](ONBOARDING-FREEMIUM.md))
- **Plus / Ilimitado:** sin tope vitalicio; 5 escaneos por hora por usuario (anti-abuso, `config/menu_scan.php`)

## Error 429 (cuota de Gemini agotada)

Google limita las peticiones gratuitas por minuto y por día. Si ves **429 Too Many Requests**:

1. Espera unos minutos y vuelve a intentar.
2. En **Plataforma → Escaneo IA**, usa `gemini-2.5-flash-lite` (recomendado). `gemini-2.0-flash` figura en la lista de Google pero devuelve HTTP 404 en `generateContent`.
3. Revisa uso y facturación en [Google AI Studio](https://aistudio.google.com/).
4. Si Tesseract está instalado, la app intentará OCR local automáticamente cuando Gemini falle.

## Error SSL en Windows (`cURL error 60`)

Si aparece *unable to get local issuer certificate*:

1. El proyecto incluye `resources/certs/cacert.pem` (bundle de autoridades).
2. `php-local.ini` ya apunta `curl.cainfo` y `openssl.cafile` a ese archivo.
3. Arranca con `run-local.ps1` (usa `-c php-local.ini`).
4. Si usas otro PHP, copia esas dos líneas a tu `php.ini` o define en `.env`:
   `MENU_SCAN_CA_BUNDLE=C:\ruta\al\proyecto\resources\certs\cacert.pem`

Solo en local, como último recurso: `MENU_SCAN_VERIFY_SSL=false` (no usar en producción).

## Privacidad

No compartas la API key. Los archivos se guardan en `storage/app/menu-scans/` y se eliminan tras importar o cancelar.
