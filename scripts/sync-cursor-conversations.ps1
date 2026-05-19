# Copia transcripts JSONL del workspace Cursor local a .cursor/conversations/ (para Git).
$ErrorActionPreference = "Stop"

$repoRoot = Split-Path $PSScriptRoot -Parent
if (-not (Test-Path (Join-Path $repoRoot "artisan"))) {
    Write-Error "No se encontró la raíz del proyecto Laravel (artisan)."
}

$destDir = Join-Path $repoRoot ".cursor\conversations"
New-Item -ItemType Directory -Force -Path $destDir | Out-Null

# Ruta típica de Cursor en Windows (workspace Webnu)
$cursorProject = Join-Path $env:USERPROFILE ".cursor\projects\c-webProject-webnu-Webnu\agent-transcripts"
if (-not (Test-Path $cursorProject)) {
    Write-Warning "No existe: $cursorProject"
    Write-Host "Ajusta la variable en este script si tu ruta de Cursor es distinta."
    exit 0
}

$copied = 0
Get-ChildItem -Path $cursorProject -Recurse -Filter "*.jsonl" | ForEach-Object {
    $id = $_.BaseName
    $short = if ($id.Length -gt 8) { $id.Substring(0, 8) } else { $id }
    $destName = "$short-$($_.LastWriteTime.ToString('yyyy-MM-dd')).jsonl"
    $destPath = Join-Path $destDir $destName

    if (-not (Test-Path $destPath) -or $_.LastWriteTimeUtc -gt (Get-Item $destPath).LastWriteTimeUtc) {
        Copy-Item -Path $_.FullName -Destination $destPath -Force
        Write-Host "Copiado: $destName"
        $copied++
    }
}

if ($copied -eq 0) {
    Write-Host "Nada nuevo que copiar (transcripts ya actualizados)."
} else {
    Write-Host "Listo. Añade al commit: git add .cursor/conversations/"
}
