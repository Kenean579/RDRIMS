<?php
$content = file_get_contents("d:/proje/qelemeda/RDRIMS/IMPLEMENTATION_PLAN.md");
preg_match_all('/### File \d+: `(.*?)`/', $content, $matches);
$files = $matches[1];
$missing = [];
foreach ($files as $f) {
    if (strpos($f, 'Run these commands') !== false) continue;
    $path = "d:/proje/qelemeda/RDRIMS/backend/" . $f;
    if (!file_exists($path)) {
        $missing[] = $f;
    }
}
echo "Missing files:\n" . implode("\n", $missing) . "\n";
