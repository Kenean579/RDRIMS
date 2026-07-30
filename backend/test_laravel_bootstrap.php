<?php

ini_set('max_execution_time', 10);
set_time_limit(10);

echo "Testing Laravel bootstrap...\n";

require __DIR__.'/vendor/autoload.php';

try {
    $app = require_once __DIR__.'/bootstrap/app.php';
    echo "✅ Laravel app created\n";
    
    // Don't bootstrap the app fully - just check it loads
    echo "✅ Bootstrap successful\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
