<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::whereHas('roles', fn($q) => $q->where('name', 'super_admin'))->first();
$token = $user->createToken('test')->plainTextToken;
echo $token;
