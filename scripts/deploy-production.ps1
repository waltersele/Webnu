# Despliegue en el mismo servidor webnu.es (Fase 3)
# Ejecutar desde la raíz del proyecto EN PRODUCCIÓN tras backup (scripts/backup-production.ps1)

param(
    [string] $ProjectRoot = (Split-Path $PSScriptRoot -Parent),
    [string] $Php = "php",
    [switch] $SkipNpm,
    [switch] $DryRun
)

$ErrorActionPreference = "Stop"
Set-Location $ProjectRoot

function Invoke-Step([string] $label, [scriptblock] $action) {
    Write-Host "→ $label" -ForegroundColor Cyan
    if ($DryRun) {
        Write-Host "  (dry-run)" -ForegroundColor DarkGray
        return
    }
    & $action
}

Write-Host "Despliegue Webnu — mismo servidor" -ForegroundColor Green
Write-Host "NO sobrescribir: .env, public/img, storage (sesiones/logs)" -ForegroundColor Yellow
Write-Host "Fusionar manualmente public/.htaccess (redirects líneas 1-9)" -ForegroundColor Yellow
Write-Host ""

$confirm = Read-Host "¿Backup MySQL + img + .htaccess hecho? (s/N)"
if ($confirm -notmatch '^[sS]') {
    Write-Host "Ejecuta primero .\scripts\backup-production.ps1" -ForegroundColor Red
    exit 1
}

Invoke-Step "Modo mantenimiento" { & $Php artisan down --render="errors/503" }

try {
    Invoke-Step "Composer (producción)" {
        composer install --no-dev --optimize-autoloader --no-interaction
    }

    if (-not $SkipNpm) {
        Invoke-Step "Assets npm" {
            npm ci 2>$null; if ($LASTEXITCODE -ne 0) { npm install }
            npm run production
        }
    }

    Invoke-Step "Migraciones" { & $Php artisan migrate --force }
    Invoke-Step "Cache" {
        & $Php artisan config:cache
        & $Php artisan route:cache
        & $Php artisan view:clear
    }
} finally {
    Invoke-Step "Fin mantenimiento" { & $Php artisan up }
}

Write-Host ""
Write-Host "Post-despliegue:" -ForegroundColor Green
Write-Host "  php artisan webnu:export-companies-inventory --with-users"
Write-Host "  Revisar sections_count/products_count en el CSV — cartas no deben quedar vacias"
Write-Host "  Login 2-3 clientes: Mi carta = mismo contenido que /carta/slug"
Write-Host "  (Opcional) Stripe cuando reactiveis suscripciones"
Write-Host "  php artisan webnu:audit-public-menus --base=https://webnu.es --csv=storage/migration-inventory/companies-....csv --legacy"
