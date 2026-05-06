<?php

use App\Http\Controllers\Api\CallController;
use App\Http\Controllers\Api\ProposalController;
use App\Models\Call;
use App\Models\CallStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
