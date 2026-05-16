<?php
$content = file_get_contents("d:/proje/qelemeda/RDRIMS/IMPLEMENTATION_PLAN.md");
$missing = ['SyncRolePermissionsRequest.php', 'AssignUserRoleRequest.php', 'StoreDetectionRequest.php', 'ApproveExpenseRequest.php'];

foreach ($missing as $file) {
    // Match the file section exactly
    $pattern = '/### File \d+: `app\/Http\/Requests\/' . preg_quote($file) . '`[\s\S]*?```php\s*(.*?)```/s';
    if (preg_match($pattern, $content, $matches)) {
        $path = "d:/proje/qelemeda/RDRIMS/backend/app/Http/Requests/" . $file;
        $code = $matches[1];
        if (strpos($code, '<?php') === false) {
            $code = "<?php\n\n" . $code;
        }
        file_put_contents($path, trim($code) . "\n");
        echo "Created $file\n";
    } else {
        echo "Could not find content for $file in IMPLEMENTATION_PLAN.md\n";
    }
}
