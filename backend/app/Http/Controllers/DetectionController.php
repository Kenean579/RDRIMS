<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDetectionRequest;
use App\Jobs\ProcessDetectionJob;
use App\Jobs\ProcessPlagiarismCheckJob;
use App\Models\DetectionService;
use App\Models\DetectionRequest;
use Illuminate\Http\JsonResponse;

class DetectionController extends Controller
{
    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $requests = DetectionRequest::with('results', 'service', 'status', 'requestedBy')
            ->hierarchical($request->user(), 'requested_by')
            ->latest()
            ->paginate(50);
        return response()->json($requests);
    }

    public function services(): JsonResponse
    {
        $services = DetectionService::all();
        return response()->json($services);
    }

    public function store(StoreDetectionRequest $request): JsonResponse
    {
        $detectionRequest = DetectionRequest::create([
            ...$request->validated(),
            'service_id' => $request->service_id ?? 1,
            'status_id' => DetectionRequest::getStatusId('pending'),
            'requested_by' => $request->user()->id,
            'requested_at' => now(),
        ]);

        // Dispatch appropriate job based on selected service
        $service = DetectionService::find($detectionRequest->service_id);
        
        if ($service && $service->name === 'plagiarismcheck') {
            ProcessPlagiarismCheckJob::dispatch($detectionRequest);
        } else {
            ProcessDetectionJob::dispatch($detectionRequest);
        }

        return response()->json(['message' => 'Detection requested.', 'request' => $detectionRequest], 202);
    }

    public function show(int $id): JsonResponse
    {
        $detectionRequest = DetectionRequest::with('results', 'service', 'status')->findOrFail($id);
        return response()->json($detectionRequest);
    }
}
