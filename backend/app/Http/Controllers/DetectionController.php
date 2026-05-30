<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDetectionRequest;
use App\Models\DetectionRequest;
use App\Jobs\ProcessDetectionJob;
use Illuminate\Http\JsonResponse;

class DetectionController extends Controller
{
    public function index(): JsonResponse
    {
        $requests = DetectionRequest::with('results', 'service', 'status')
            ->latest()
            ->get();
        return response()->json($requests);
    }

    public function store(StoreDetectionRequest $request): JsonResponse
    {
        $detectionRequest = DetectionRequest::create([
            ...$request->validated(),
            'status_id' => DetectionRequest::getStatusId('pending'),
            'requested_by' => $request->user()->id,
            'requested_at' => now(),
        ]);

        ProcessDetectionJob::dispatch($detectionRequest);

        return response()->json(['message' => 'Detection requested.', 'request' => $detectionRequest], 202);
    }

    public function show(int $id): JsonResponse
    {
        $detectionRequest = DetectionRequest::with('results', 'service', 'status')->findOrFail($id);
        return response()->json($detectionRequest);
    }
}
