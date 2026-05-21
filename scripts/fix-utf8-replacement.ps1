$utf8 = New-Object System.Text.UTF8Encoding $false
$rep = [char]0xFFFD
$o = [char]0x00F3
$nTilde = [char]0x00F1
$u = [char]0x00FA
$e = [char]0x00E9
$pairs = @(
    @("A${rep}adir", "A${nTilde}adir"),
    @("Descripci${rep}n", "Descripci${o}n"),
    @("raci${rep}n", "raci${o}n"),
    @("n${rep}meros", "n${u}meros"),
    @("Al${rep}rgenos", "Al${e}rgenos"),
    @("secci${rep}n", "secci${o}n"),
    @("Secci${rep}n", "Secci$([char]0x00D3)n"),
    @("${rep}Est${rep}s", "$([char]0x00BF)Est$([char]0x00E1)s")
)
$files = Get-ChildItem -Path "$PSScriptRoot\..\resources\views\admin" -Filter *.blade.php -Recurse
foreach ($path in $files) {
    $c = [System.IO.File]::ReadAllText($path.FullName, $utf8)
    if ($c.IndexOf($rep) -lt 0) { continue }
    $orig = $c
    foreach ($p in $pairs) {
        $c = $c.Replace($p[0], $p[1])
    }
    if ($c -ne $orig) {
        [System.IO.File]::WriteAllText($path.FullName, $c, $utf8)
        Write-Host "Fixed: $($path.Name)"
    }
}
