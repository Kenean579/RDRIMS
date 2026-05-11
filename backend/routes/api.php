<?php

use App\Http\Controllers\Api\CallController;
use App\Http\Controllers\Api\ProposalController;
use App\Models\Call;
use App\Models\CallStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

<<<<<<< HEAD
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Calls resourceful endpoints
    Route::apiResource('calls', CallController::class);

    // Optional extra endpoint to close a call manually
    Route::post('/calls/{call}/close', function (Call $call) {
      $closedId = CallStatus::where('name', 'closed')->value('id');
     $call->update(['status_id' => $closedId])
        return response()->json(['success' => true, 'message' => 'Call closed.']);
    })->middleware('role:admin,research_admin');
});
// Proposal CRUD
    Route::apiResource('proposals', ProposalController::class);

    // Proposal workflow
    Route::post('/proposals/{proposal}/submit', [ProposalController::class, 'submit']);
    Route::post('/proposals/{proposal}/assign-reviewers', [ProposalController::class, 'assignReviewers']);
    Route::get('/proposals/{proposal}/suggest-reviewers', [ProposalController::class, 'suggestReviewers']);
    Route::post('/proposals/{proposal}/approve', [ProposalController::class, 'approve']);

    // Finance checks
    Route::post('/proposals/{proposal}/finance-checks', [ProposalController::class, 'storeFinanceCheck']);

    // Ethics requests
    Route::post('/proposals/{proposal}/ethics-requests', [ProposalController::class, 'storeEthicsRequest']);

    // Detection
    Route::post('/proposals/{proposal}/detection', [ProposalController::class, 'requestDetection']);
    Route::get('/proposals/{proposal}/detection/{detectionRequest}', [ProposalController::class, 'getDetectionResult']);
});
=======
// ──────────────────────────────────────────
// HERMELA – Projects, Outputs, TT, Industry,
//           Financial, Events, Publications,
//           Community, Reports, Attachments
// ──────────────────────────────────────────
// Projects, Milestones, Tasks
Route::apiResource('projects',                 App\Http\Controllers\ProjectController::class);
Route::apiResource('projects.milestones',      App\Http\Controllers\MilestoneController::class);
Route::apiResource('milestones.tasks',         App\Http\Controllers\TaskController::class);

// Outputs
Route::apiResource('outputs',                  App\Http\Controllers\OutputController::class);
Route::post('outputs/{output}/status',         [App\Http\Controllers\OutputController::class, 'changeStatus']);
Route::apiResource('outputs.participants',     App\Http\Controllers\OutputParticipantController::class)
    ->only(['index', 'store', 'destroy']);

// Patents & Licenses
Route::apiResource('patents',                  App\Http\Controllers\PatentController::class);
Route::apiResource('patents.licenses',         App\Http\Controllers\LicenseController::class);

// Partners & MoUs
Route::apiResource('partners',                 App\Http\Controllers\PartnerController::class);
Route::apiResource('partners.mo-us',           App\Http\Controllers\MoUController::class);

// Expenses
Route::apiResource('projects.expenses',        App\Http\Controllers\ExpenseController::class);
Route::put('expenses/{expense}/approve',       [App\Http\Controllers\ExpenseController::class, 'approve']);

// Events
Route::apiResource('events',                   App\Http\Controllers\EventController::class);
Route::post('events/{event}/register',         [App\Http\Controllers\EventRegistrationController::class, 'register']);
Route::put('events/{event}/attendance',        [App\Http\Controllers\EventRegistrationController::class, 'markAttendance']);
Route::post('events/{event}/certificates',     [App\Http\Controllers\EventRegistrationController::class, 'generateCertificates']);

// Publications
Route::apiResource('publications',             App\Http\Controllers\PublicationController::class);
Route::apiResource('publications.authors',     App\Http\Controllers\PublicationAuthorController::class);

// Community Problems
Route::apiResource('community-problems',       App\Http\Controllers\CommunityProblemController::class);
Route::post('community-problems/{problem}/claim',    [App\Http\Controllers\CommunityProblemController::class, 'claim']);
Route::post('community-problems/{problem}/complete', [App\Http\Controllers\CommunityProblemController::class, 'complete']);
Route::post('community-problems/{problem}/feedback', [App\Http\Controllers\CommunityProblemController::class, 'addFeedback']);

// Reports
Route::get('reports',                    [App\Http\Controllers\ReportController::class, 'index']);
Route::post('reports/generate',          [App\Http\Controllers\ReportController::class, 'generate']);
Route::get('reports/{report}/download',  [App\Http\Controllers\ReportController::class, 'download']);

// File attachments for project/output/patent/agreement
Route::post('projects/{project}/files',          [App\Http\Controllers\ProjectFileController::class, 'attach']);
Route::delete('projects/{project}/files/{file}', [App\Http\Controllers\ProjectFileController::class, 'detach']);

Route::post('outputs/{output}/files',            [App\Http\Controllers\OutputFileController::class, 'attach']);
Route::delete('outputs/{output}/files/{file}',   [App\Http\Controllers\OutputFileController::class, 'detach']);

Route::post('patents/{patent}/files',            [App\Http\Controllers\PatentFileController::class, 'attach']);
Route::delete('patents/{patent}/files/{file}',   [App\Http\Controllers\PatentFileController::class, 'detach']);

Route::post('agreements/files/{parentType}/{parent}', [App\Http\Controllers\AgreementFileController::class, 'attach']);
Route::delete('agreements/files/{parentType}/{parent}/{file}', [App\Http\Controllers\AgreementFileController::class, 'detach']);
>>>>>>> main
