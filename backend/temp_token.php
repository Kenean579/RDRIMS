<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pivot = \App\Models\ProposalReviewer::where('proposal_id', 3)->first();
$user = \App\Models\User::find($pivot->reviewer_id);
$token = $user->createToken('test')->plainTextToken;
echo $token;
