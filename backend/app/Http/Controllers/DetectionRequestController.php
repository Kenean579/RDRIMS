<?php

namespace App\Http\Controllers;

use App\Models\DetectionRequest;
use App\Models\Proposal;
use App\Http\Requests\DetectionRequestFormRequest;
use App\Services\DetectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DetectionRequestController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private DetectionService $detectionService) {}

    public function store(DetectionRequestFormRequest $request): JsonResponse
    {
        $proposal = Proposal::findOrFail($request->proposal_id);
        $this->authorize('update', $proposal);

        $detectionRequest = $this->detectionService->submitRequest($proposal, $request->user());
        return response()->json($detectionRequest, 201);
    }

    public function update(Request $request, DetectionRequest $detectionRequest): JsonResponse
    {
        $this->authorize('update', $detectionRequest);
        $request->validate(['similarity_score' => 'required|numeric', 'report_url' => 'nullable|string']);

        $this->detectionService->complete($detectionRequest, $request->similarity_score, $request->report_url);
        return response()->json($detectionRequest);
    }
}
