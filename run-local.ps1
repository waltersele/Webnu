# Arranca Webnu en local con SQLite (solo vista previa; no usa la BD de producción)
# PHP 8.1+ recomendado (8.3 ideal). Ajusta la ruta en $PhpCandidates si hace falta.
$PhpCandidates = @(
    "C:\php\php.exe",
    "C:\php83\php.exe",
    "C:\xampp\php\php.exe",
    "C:\laragon\bin\php\php-8.3.12-Win32-vs16-x64\php.exe",
    "C:\laragon\bin\php\php-8.3.0-Win32-vs16-x64\php.exe"
)
$Php = $null
foreach ($candidate in $PhpCandidates) {
    if (Test-Path $candidate) {
        $Php = $candidate
        break
    }
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
# Inyectar rutas CA en php-local.ini (Guzzle / Gemini)
$iniContent = Get-Content $Ini -Raw -ErrorAction SilentlyContinue
if ($iniContent -notmatch 'curl\.cainfo') {
    Add-Content -Path $Ini -Value "`nopenssl.cafile = `"$CaPem`"`ncurl.cainfo = `"$CaPem`"`n"
} else {
    $iniContent = $iniContent -replace 'openssl\.cafile\s*=.*', "openssl.cafile = `"$CaPem`""
    $iniContent = $iniContent -replace 'curl\.cainfo\s*=.*', "curl.cainfo = `"$CaPem`""
    Set-Content -Path $Ini -Value $iniContent -NoNewline
}

if (-not $Php) {
    Write-Host "No se encontró PHP 8.1+. Instala PHP 8.3 o ajusta PhpCandidates en run-local.ps1." -ForegroundColor Red
    exit 1
}

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

Set-Location $PSScriptRoot

& $Php -c $Ini artisan migrate --force
& $Php -c $Ini scripts/seed-local-demo.php
& $Php -c $Ini scripts/fix-demo-ownership.php 2>$null
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
