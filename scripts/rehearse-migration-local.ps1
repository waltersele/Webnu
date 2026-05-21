# Ensayo local de migración (Fase 2 del plan) — importar dump de prod y validar cartas
# Uso:
#   1. Coloca el dump en storage/backups/import/webnu-prod.sql
#   2. Copia public/img del backup a public/img (fusionar, no borrar)
#   3. .\scripts\rehearse-migration-local.ps1

param(
    [string] $ProjectRoot = (Split-Path $PSScriptRoot -Parent),
    [string] $SqlDump = "",
    [string] $Php = ""
)

$ErrorActionPreference = "Stop"
Set-Location $ProjectRoot

if (-not $Php) {
    foreach ($c in @("C:\xampp\php\php.exe", "C:\php\php.exe", "php")) {
        if ($c -eq "php" -or (Test-Path $c)) { $Php = $c; break }
    }
}

if (-not $SqlDump) {
    $SqlDump = Join-Path $ProjectRoot "storage\backups\import\webnu-prod.sql"
}

if (-not (Test-Path $SqlDump)) {
    Write-Host "Coloca el dump de producción en:" -ForegroundColor Yellow
    Write-Host "  storage\backups\import\webnu-prod.sql"
    Write-Host "O pasa -SqlDump ruta\al\archivo.sql"
    exit 1
}

Write-Host "Ensayo migración local" -ForegroundColor Cyan
Write-Host "  PHP: $Php"
Write-Host "  Dump: $SqlDump"

# Ajusta .env a MySQL local antes de importar (DB_*). Este script no modifica .env automáticamente.
Write-Host ""
Write-Host "Pasos manuales previos:" -ForegroundColor Yellow
Write-Host "  1. .env con MySQL local y APP_URL=https://webnu.es (o http://127.0.0.1:8000)"
Write-Host "  2. mysql -u root -p nombre_bd < storage/backups/import/webnu-prod.sql"
Write-Host "  3. Copiar public/img del backup de prod"
Write-Host ""
$confirm = Read-Host "¿Dump ya importado y img copiada? (s/N)"
if ($confirm -notmatch '^[sS]') {
    Write-Host "Importa el dump y vuelve a ejecutar." -ForegroundColor Yellow
    exit 0
}

& $Php artisan migrate --force
& $Php artisan config:clear
& $Php artisan view:clear

& $Php artisan webnu:export-companies-inventory --with-users
$csv = Get-ChildItem (Join-Path $ProjectRoot "storage\migration-inventory\companies-*.csv") | Sort-Object LastWriteTime -Descending | Select-Object -First 1
if ($csv) {
    Write-Host ""
    Write-Host "Auditoría HTTP (servidor local debe estar en marcha: php artisan serve)" -ForegroundColor Cyan
    & $Php artisan webnu:audit-public-menus --base=http://127.0.0.1:8000 --csv=$($csv.FullName)
}

Write-Host ""
Write-Host "Revisa login de 2-3 cuentas en /admin y Mi carta." -ForegroundColor Green
