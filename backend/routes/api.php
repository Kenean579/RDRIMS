<?php

use App\Http\Controllers\Api\CallController;
use App\Models\Call;
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
        $call->update(['status_id' => 3]); // closed status id = 3
        return response()->json(['success' => true, 'message' => 'Call closed.']);
    })->middleware('role:admin,research_admin');
});
