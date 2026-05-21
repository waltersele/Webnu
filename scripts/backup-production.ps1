# Backup pre-migración Webnu (ejecutar EN EL SERVIDOR de producción o con rutas ajustadas)
# Uso: .\scripts\backup-production.ps1 -ProjectRoot "C:\ruta\webnu" -MySqlDump "C:\xampp\mysql\bin\mysqldump.exe"

param(
    [Parameter(Mandatory = $false)]
    [string] $ProjectRoot = (Split-Path $PSScriptRoot -Parent),
    [string] $MySqlDump = "mysqldump",
    [string] $DbHost = "127.0.0.1",
    [string] $DbName = "",
    [string] $DbUser = "",
    [string] $DbPassword = ""
)

$ErrorActionPreference = "Stop"
$stamp = Get-Date -Format "yyyy-MM-dd-HHmm"
$backupRoot = Join-Path $ProjectRoot "storage\backups\pre-migration-$stamp"
New-Item -ItemType Directory -Force -Path $backupRoot | Out-Null

Write-Host "Backup Webnu → $backupRoot" -ForegroundColor Cyan

# Leer credenciales desde .env si no se pasaron
$envFile = Join-Path $ProjectRoot ".env"
if (Test-Path $envFile) {
    $envLines = Get-Content $envFile
    foreach ($line in $envLines) {
        if ($line -match '^\s*DB_DATABASE=(.+)$') { if (-not $DbName) { $DbName = $matches[1].Trim('"') } }
        if ($line -match '^\s*DB_USERNAME=(.+)$') { if (-not $DbUser) { $DbUser = $matches[1].Trim('"') } }
        if ($line -match '^\s*DB_PASSWORD=(.*)$') { if (-not $DbPassword) { $DbPassword = $matches[1].Trim('"') } }
        if ($line -match '^\s*DB_HOST=(.+)$') { $DbHost = $matches[1].Trim('"') }
    }
}

if (-not $DbName) {
    Write-Host "Define DB_DATABASE en .env o pasa -DbName" -ForegroundColor Red
    exit 1
}

$sqlFile = Join-Path $backupRoot "database-$stamp.sql"
$env:MYSQL_PWD = $DbPassword
& $MySqlDump -h $DbHost -u $DbUser $DbName | Set-Content -Encoding UTF8 $sqlFile
Remove-Item Env:\MYSQL_PWD -ErrorAction SilentlyContinue
Write-Host "  MySQL: $sqlFile"

$imgSrc = Join-Path $ProjectRoot "public\img"
$imgDst = Join-Path $backupRoot "public-img"
if (Test-Path $imgSrc) {
    Copy-Item -Path $imgSrc -Destination $imgDst -Recurse -Force
    Write-Host "  public/img copiado"
} else {
    Write-Host "  AVISO: no existe public/img" -ForegroundColor Yellow
}

$htaccess = Join-Path $ProjectRoot "public\.htaccess"
if (Test-Path $htaccess) {
    Copy-Item $htaccess (Join-Path $backupRoot ".htaccess") -Force
    Write-Host "  .htaccess copiado"
}

$envCopy = Join-Path $backupRoot ".env.backup"
if (Test-Path $envFile) {
    Copy-Item $envFile $envCopy -Force
    Write-Host "  .env copiado (no subir a git)"
}

Write-Host ""
Write-Host "Backup listo. Conserva esta carpeta hasta validar la migración." -ForegroundColor Green
Write-Host "Inventario CSV: php artisan webnu:export-companies-inventory --with-users"
