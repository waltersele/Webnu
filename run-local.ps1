# Arranca Webnu en local con SQLite (solo vista previa; no usa la BD de producción)
# PHP 8.1+ recomendado (8.3 ideal). Ajusta la ruta en $PhpCandidates si hace falta.
# Laravel 10 en main requiere PHP 8.1+ (composer.json: ^8.3). Priorizar 8.3 del sistema.
$PhpCandidates = @(
    "C:\php83\php.exe",
    "C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe",
    "C:\laragon\bin\php\php-8.3.12-Win32-vs16-x64\php.exe",
    "C:\laragon\bin\php\php-8.3.0-Win32-vs16-x64\php.exe",
    "C:\xampp\php\php.exe",
    "C:\php\php.exe",
    (Join-Path $PSScriptRoot ".php-runtime\php.exe"),
    (Join-Path $PSScriptRoot ".php-runtime74\php.exe")
)
$Php = $null
foreach ($candidate in $PhpCandidates) {
    if (-not (Test-Path $candidate)) { continue }
    $verLine = & $candidate -r "echo PHP_VERSION;" 2>$null
    if (-not $verLine) { continue }
    $ver = [version]$verLine
    if ($ver.Major -lt 8 -or ($ver.Major -eq 8 -and $ver.Minor -lt 1)) {
        continue
    }
    $Php = $candidate
    break
}
$Ini = Join-Path $PSScriptRoot "php-local.ini"
$Sqlite = Join-Path $PSScriptRoot "database\database.sqlite"
$CaPem = Join-Path $PSScriptRoot "resources\certs\cacert.pem"

if (-not (Test-Path $CaPem)) {
    New-Item -ItemType Directory -Force -Path (Split-Path $CaPem) | Out-Null
    Write-Host "Descargando certificados CA para HTTPS..." -ForegroundColor Cyan
    Invoke-WebRequest -Uri "https://curl.se/ca/cacert.pem" -OutFile $CaPem -UseBasicParsing
}
$env:SSL_CERT_FILE = $CaPem
$env:CURL_CA_BUNDLE = $CaPem

if (-not $Php) {
    Write-Host "No se encontró PHP 8.1+. Instala PHP 8.3 o ajusta PhpCandidates en run-local.ps1." -ForegroundColor Red
    exit 1
}

# Resolver carpeta de extensiones del PHP detectado (XAMPP, php-runtime, etc.)
$ExtDir = Join-Path (Split-Path $Php) 'ext'
if (-not (Test-Path $ExtDir)) {
    Write-Host "Aviso: no existe $ExtDir; las extensiones PHP no cargarán." -ForegroundColor Yellow
}

# Inyectar rutas CA y extension_dir en php-local.ini (Guzzle / Gemini / PDO)
$iniContent = Get-Content $Ini -Raw -ErrorAction SilentlyContinue
if ($iniContent -notmatch 'curl\.cainfo') {
    Add-Content -Path $Ini -Value "`nopenssl.cafile = `"$CaPem`"`ncurl.cainfo = `"$CaPem`"`n"
    $iniContent = Get-Content $Ini -Raw -ErrorAction SilentlyContinue
} else {
    $iniContent = $iniContent -replace 'openssl\.cafile\s*=.*', "openssl.cafile = `"$CaPem`""
    $iniContent = $iniContent -replace 'curl\.cainfo\s*=.*', "curl.cainfo = `"$CaPem`""
}
$iniContent = $iniContent -replace 'extension_dir\s*=.*', "extension_dir = `"$ExtDir`""
Set-Content -Path $Ini -Value $iniContent -NoNewline

$phpVersion = & $Php -r "echo PHP_VERSION;"
Write-Host "PHP: $Php ($phpVersion)" -ForegroundColor Cyan

if (-not (Test-Path $Sqlite)) {
    New-Item -ItemType File -Path $Sqlite -Force | Out-Null
}

$env:DB_CONNECTION = "sqlite"
$env:DB_DATABASE = $Sqlite
$env:APP_URL = "http://127.0.0.1:8000"
$env:APP_ENV = "local"
$env:APP_DEBUG = "true"
$env:SUPER_ADMIN_EMAILS = "demo@webnu.local"
$env:MENU_SCAN_SCANS_PER_HOUR = "30"
$env:DIGITAL_SIGNAGE_APP_KEY = "dev-signage-key-compartida"
$env:TVPIK_ALLOWED_REDIRECT_URIS = "http://127.0.0.1:8001/api/v1/integrations/webnu/callback,http://localhost:8001/api/v1/integrations/webnu/callback"
$env:TVPIK_API_URL = "http://127.0.0.1:8001"

Set-Location $PSScriptRoot

# .env con BOM UTF-8 rompe vlucas/phpdotenv y deja respuestas vacías en el navegador
$envPath = Join-Path $PSScriptRoot ".env"
if (Test-Path $envPath) {
    $bytes = [System.IO.File]::ReadAllBytes($envPath)
    if ($bytes.Length -ge 3 -and $bytes[0] -eq 0xEF -and $bytes[1] -eq 0xBB -and $bytes[2] -eq 0xBF) {
        $text = [System.Text.Encoding]::UTF8.GetString($bytes, 3, $bytes.Length - 3)
        $utf8NoBom = New-Object System.Text.UTF8Encoding $false
        [System.IO.File]::WriteAllText($envPath, $text, $utf8NoBom)
        Write-Host "Aviso: se eliminó BOM UTF-8 de .env (causa pantalla en blanco)." -ForegroundColor Yellow
    }
}

# Tras git pull: vendor puede ser Laravel 7 mientras el código es Laravel 10.
$l10FrameworkMarker = Join-Path $PSScriptRoot "vendor\laravel\framework\src\Illuminate\Foundation\Support\Providers\RouteServiceProvider.php"
if (-not (Test-Path $l10FrameworkMarker)) {
    Write-Host "Dependencias desactualizadas (Laravel 10 en composer.json). Ejecutando composer install..." -ForegroundColor Cyan
    $composerPhar = $null
    foreach ($cp in @(
        (Join-Path $PSScriptRoot "composer.phar"),
        "C:\laragon\bin\composer\composer.phar"
    )) {
        if (Test-Path $cp) { $composerPhar = $cp; break }
    }
    if (-not $composerPhar) {
        $composerCmd = Get-Command composer -ErrorAction SilentlyContinue
        if (-not $composerCmd) {
            Write-Host "No se encontró Composer. Coloca composer.phar en el proyecto o instálalo." -ForegroundColor Red
            exit 1
        }
        & $Php $composerCmd.Source install --no-interaction --prefer-dist
    } else {
        & $Php $composerPhar install --no-interaction --prefer-dist
        if ($LASTEXITCODE -ne 0) {
            Write-Host "composer install falló; probando composer update..." -ForegroundColor Yellow
            & $Php $composerPhar update --no-interaction --prefer-dist
        }
    }
    if ($LASTEXITCODE -ne 0) {
        Write-Host "Composer falló. Necesitas PHP 8.1+ (ideal 8.3) y extension zip habilitada." -ForegroundColor Red
        exit 1
    }
}

& $Php -c $Ini artisan migrate --force
& $Php -c $Ini scripts/seed-local-demo.php
& $Php -c $Ini scripts/fix-demo-ownership.php 2>$null
& $Php -c $Ini scripts/seed-demo-translations.php 2>$null
& $Php -c $Ini scripts/seed-platform-roles.php 2>$null
& $Php -c $Ini scripts/seed-platform-demo.php
& $Php -c $Ini scripts/fix-gemini-model.php 2>$null
& $Php -c $Ini artisan config:clear 2>$null
& $Php -c $Ini artisan storage:link 2>$null

$env:PHPRC = $Ini

Write-Host ""
Write-Host "Webnu en local:" -ForegroundColor Green
Write-Host "  Inicio:     http://127.0.0.1:8000"
Write-Host "  Admin:      http://127.0.0.1:8000/admin"
Write-Host "  Login:      demo@webnu.local / demo123"
Write-Host "  Carta demo: http://127.0.0.1:8000/carta/demo"
Write-Host ""
Write-Host "Escaneo de carta: configura GEMINI_API_KEY en .env"
Write-Host "Plataforma:  demo@webnu.local / demo123  ->  /admin/platform"
Write-Host "             Ver docs/CREDENCIALES-DEMO-LOCAL.md"
Write-Host ""
Write-Host "Pulsa Ctrl+C para detener el servidor."
Write-Host ""

& $Php -c $Ini -S 127.0.0.1:8000 -t public server.php
