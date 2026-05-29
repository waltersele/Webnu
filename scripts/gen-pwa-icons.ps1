$ErrorActionPreference = 'Stop'

Add-Type -AssemblyName System.Drawing

function New-PwaIcon {
    param(
        [Parameter(Mandatory = $true)][int]$Size,
        [Parameter(Mandatory = $true)][string]$LogoPath,
        [Parameter(Mandatory = $true)][string]$OutPath,
        [string]$Background = '#004ac6'
    )

    $bg = [System.Drawing.ColorTranslator]::FromHtml($Background)
    $bmp = New-Object System.Drawing.Bitmap($Size, $Size)
    $g = [System.Drawing.Graphics]::FromImage($bmp)
    $g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::HighQuality
    $g.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
    $g.PixelOffsetMode = [System.Drawing.Drawing2D.PixelOffsetMode]::HighQuality
    $g.Clear($bg)

    $logo = [System.Drawing.Image]::FromFile($LogoPath)
    try {
        $maxW = [int]($Size * 0.78)
        $maxH = [int]($Size * 0.34)
        $scale = [Math]::Min($maxW / $logo.Width, $maxH / $logo.Height)
        $w = [int]([Math]::Round($logo.Width * $scale))
        $h = [int]([Math]::Round($logo.Height * $scale))
        if ($w -lt 1) { $w = 1 }
        if ($h -lt 1) { $h = 1 }

        $x = [int](($Size - $w) / 2)
        $y = [int](($Size - $h) / 2)
        $g.DrawImage($logo, $x, $y, $w, $h)
    } finally {
        $logo.Dispose()
    }

    $dir = Split-Path -Parent $OutPath
    if (-not (Test-Path $dir)) {
        New-Item -ItemType Directory -Path $dir | Out-Null
    }
    $bmp.Save($OutPath, [System.Drawing.Imaging.ImageFormat]::Png)

    $g.Dispose()
    $bmp.Dispose()
}

$repoRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$logoPath = (Resolve-Path (Join-Path $repoRoot 'public/adminlte/img/logo-white.png')).Path

New-PwaIcon -Size 192 -LogoPath $logoPath -OutPath (Join-Path $repoRoot 'public/img/pwa/icon-192.png')
New-PwaIcon -Size 512 -LogoPath $logoPath -OutPath (Join-Path $repoRoot 'public/img/pwa/icon-512.png')

Write-Output 'OK'

