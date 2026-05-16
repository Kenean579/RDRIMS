$content = Get-Content "d:\proje\qelemeda\RDRIMS\IMPLEMENTATION_PLAN.md"
$files = @()
foreach ($line in $content) {
    if ($line -match "### File \d+: \`(.+?)\`") {
        $files += $matches[1]
    }
}
$missing = @()
foreach ($f in $files) {
    if ($f -match "Run these commands") { continue }
    $path = "d:\proje\qelemeda\RDRIMS\backend\$f"
    if (-not (Test-Path $path)) {
        $missing += $f
    }
}
$missing
