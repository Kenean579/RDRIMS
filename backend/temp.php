<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$proposal = \App\Models\Proposal::find(3);
$pivot = \App\Models\ProposalReviewer::where('proposal_id', 3)->first();
$user = \App\Models\User::find($pivot->reviewer_id);

try {
    $controller = app(\App\Http\Controllers\ReviewerProposalController::class);
    $request = \Illuminate\Http\Request::create('/api/reviewer/proposals/3/template', 'GET');
    $request->setUserResolver(function() use ($user) { return $user; });
    $res = $controller->downloadTemplate($proposal, $request);
    
    // Execute the callback to see the actual error!
    if ($res instanceof \Symfony\Component\HttpFoundation\StreamedResponse) {
        ob_start();
        $res->sendContent();
        ob_end_clean();
        echo "Success";
    } else {
        echo "Not a StreamedResponse";
    }
} catch (\Throwable $e) {
    echo "ERROR CAUGHT:\n";
    echo $e->getMessage() . "\n" . $e->getTraceAsString();
}
