# Arranca Webnu en local con SQLite (solo vista previa; no usa la BD de producción)
# Requiere XAMPP PHP 7.4 con php.ini (extension_dir absoluto en C:\xampp\php\ext)
$Php = "C:\xampp\php\php.exe"
$Ini = Join-Path $PSScriptRoot "php-local.ini"
$Sqlite = Join-Path $PSScriptRoot "database\database.sqlite"

if (-not (Test-Path $Php)) {
    Write-Host "No se encontró PHP en $Php. Instala XAMPP o ajusta la ruta en run-local.ps1."
    exit 1
}

if (-not (Test-Path $Sqlite)) {
    New-Item -ItemType File -Path $Sqlite -Force | Out-Null
}

$env:DB_CONNECTION = "sqlite"
$env:DB_DATABASE = $Sqlite
$env:APP_URL = "http://127.0.0.1:8000"
$env:APP_ENV = "local"
$env:APP_DEBUG = "true"

Set-Location $PSScriptRoot

& $Php -c $Ini artisan migrate --force
& $Php -c $Ini scripts/seed-local-demo.php
& $Php -c $Ini scripts/fix-demo-ownership.php 2>$null
& $Php -c $Ini artisan storage:link 2>$null

$env:PHPRC = $Ini

Write-Host ""
Write-Host "Webnu en local:" -ForegroundColor Green
Write-Host "  Inicio:     http://127.0.0.1:8000"
Write-Host "  Admin:      http://127.0.0.1:8000/admin"
Write-Host "  Login:      demo@webnu.local / demo123"
Write-Host "  Carta demo: http://127.0.0.1:8000/carta/demo"
Write-Host ""
Write-Host "Pulsa Ctrl+C para detener el servidor."
Write-Host ""

& $Php -S 127.0.0.1:8000 -t public server.php
