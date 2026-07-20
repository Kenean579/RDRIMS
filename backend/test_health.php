<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/api/test-health-index', 'GET');
$response = $kernel->handle($request);
echo "ADMIN ENDPOINT:\n";
echo $response->getContent();
echo "\n\n";

$request2 = Illuminate\Http\Request::create('/api/system/health', 'GET');
$response2 = $kernel->handle($request2);
echo "PUBLIC PING ENDPOINT:\n";
echo $response2->getContent();
echo "\n";

  




