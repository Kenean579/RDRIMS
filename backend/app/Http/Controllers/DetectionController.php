<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDetectionRequest;
use App\Http\Resources\DetectionRequestResource;
use App\Jobs\ProcessDetectionJob;
use App\Jobs\ProcessPlagiarismCheckJob;
use App\Models\DetectionService as DetectionServiceModel;
use App\Models\DetectionRequest;
use App\Services\DetectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DetectionController extends Controller
{
    public function __construct(
        private DetectionService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        // Authorization: user must have proper roles
        $this->authorize('viewAny', DetectionRequest::class);

        $requests = DetectionRequest::with('results', 'service', 'status', 'requestedBy', 'completedBy', 'reviewedBy')
            ->hierarchical($request->user(), 'requested_by')
            ->latest()
            ->paginate(50);

        return response()->json(DetectionRequestResource::collection($requests)->response()->getData(true));
    }

    public function services(): JsonResponse
    {
        $services = DetectionServiceModel::all();
        return response()->json($services);
    }

    public function store(StoreDetectionRequest $request): JsonResponse
    {
        // Authorization: form request validates authorization
        try {
            $detectionRequest = $this->service->createRequest(
                $request->validated(),
                $request->user()
            );

            // Dispatch appropriate job based on selected service
            $service = DetectionServiceModel::find($detectionRequest->service_id);

            if ($service && $service->name === 'plagiarismcheck') {
                ProcessPlagiarismCheckJob::dispatch($detectionRequest);
            } else {
                ProcessDetectionJob::dispatch($detectionRequest);
            }

            return response()->json([
                'message' => 'Detection requested.',
                'request' => new DetectionRequestResource($detectionRequest)
            ], 202);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function show(int $id): JsonResponse
    {
        $detectionRequest = DetectionRequest::with('results', 'service', 'status', 'requestedBy', 'completedBy', 'reviewedBy')
            ->findOrFail($id);

        // Authorization check
        $this->authorize('view', $detectionRequest);

        return response()->json(new DetectionRequestResource($detectionRequest));
    }

    public function complete(int $id, Request $request): JsonResponse
    {
        $detectionRequest = DetectionRequest::findOrFail($id);

        // Authorization check
        $this->authorize('complete', $detectionRequest);

        try {
            $data = $request->validate([
                'similarity_score' => 'required|numeric|min:0|max:100',
                'ai_probability' => 'sometimes|numeric|min:0|max:100',
                'report_data' => 'sometimes|json',
            ]);

            $detectionRequest = $this->service->completeRequest(
                $detectionRequest,
                $data['similarity_score'],
                $data['ai_probability'] ?? null,
                $data['report_data'] ?? null,
                $request->user()
            );

            return response()->json([
                'message' => 'Detection completed.',
                'request' => new DetectionRequestResource($detectionRequest)
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function markReviewed(int $id): JsonResponse
    {
        $detectionRequest = DetectionRequest::findOrFail($id);

        // Authorization check
        $this->authorize('markReviewed', $detectionRequest);

        try {
            $detectionRequest = $this->service->markReviewed($detectionRequest, auth()->user());

            return response()->json([
                'message' => 'Detection marked as reviewed.',
                'request' => new DetectionRequestResource($detectionRequest)
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function retry(int $id): JsonResponse
    {
        $detectionRequest = DetectionRequest::findOrFail($id);

        // Authorization check
        $this->authorize('retry', $detectionRequest);

        try {
            $detectionRequest = $this->service->retryRequest($detectionRequest, auth()->user());

            // Dispatch job to reprocess
            $service = DetectionServiceModel::find($detectionRequest->service_id);

            if ($service && $service->name === 'plagiarismcheck') {
                ProcessPlagiarismCheckJob::dispatch($detectionRequest);
            } else {
                ProcessDetectionJob::dispatch($detectionRequest);
            }

            return response()->json([
                'message' => 'Detection request retried.',
                'request' => new DetectionRequestResource($detectionRequest)
            ], 202);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $detectionRequest = DetectionRequest::findOrFail($id);

        // Authorization check
        $this->authorize('delete', $detectionRequest);

        $detectionRequest->delete();

        return response()->json(['message' => 'Detection request deleted.']);
    }

    public function restore(int $id): JsonResponse
    {
        $detectionRequest = DetectionRequest::withTrashed()->findOrFail($id);

        // Authorization check
        $this->authorize('restore', $detectionRequest);

        $detectionRequest->restore();

        return response()->json([
            'message' => 'Detection request restored.',
            'request' => new DetectionRequestResource($detectionRequest)
        ]);
    }
}
