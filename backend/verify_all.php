<?php
$content = file_get_contents("d:/proje/qelemeda/RDRIMS/IMPLEMENTATION_PLAN.md");

// Match all file declarations in the plan
preg_match_all('/### File \w+: `?([^`\r\n]+)`?/m', $content, $matches);
$files = $matches[1];

$missing = [];
$totalFiles = count($files);

foreach ($files as $f) {
    // Skip command blocks or non-paths
    if (strpos($f, 'Run these commands') !== false || strpos($f, 'bash') !== false) {
        $totalFiles--;
        continue;
    }
    
    $path = "d:/proje/qelemeda/RDRIMS/backend/" . trim($f);
    if (!file_exists($path)) {
        $missing[] = trim($f);
    }
}

echo "Total distinct files expected: " . $totalFiles . "\n";
echo "Missing files count: " . count($missing) . "\n";
if (count($missing) > 0) {
    echo "Missing files list:\n";
    foreach ($missing as $m) {
        echo "- " . $m . "\n";
    }
} else {
    echo "All files accounted for!\n";
}
