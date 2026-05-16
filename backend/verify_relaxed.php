<?php
$content = file_get_contents('d:/proje/qelemeda/RDRIMS/IMPLEMENTATION_PLAN.md');
preg_match_all('/### File[^:]*:\s*`?([^`\r\n]+)`?/', $content, $m);
$missing = 0;
foreach($m[1] as $f) {
    if(strpos($f, 'Run these commands') !== false) continue;
    if(!file_exists('d:/proje/qelemeda/RDRIMS/backend/'.trim($f))) {
        echo "Missing: ".trim($f)."\n";
        $missing++;
    }
}
echo "Total missing: $missing\n";
