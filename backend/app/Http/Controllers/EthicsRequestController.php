<?php

namespace App\Http\Controllers;

use App\Models\EthicsRequest;
use App\Models\Proposal;
use App\Services\EthicsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EthicsRequestController extends Controller
{
    public function __construct(private EthicsService $ethicsService) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json(
            EthicsRequest::with('proposal', 'approvalStatus')
                ->whereHas('proposal', fn($q) => $q->hierarchical($request->user(), 'submitted_by'))
                ->latest()
                ->paginate(20)
        );
    }

    public function store(Request $request, Proposal $proposal): JsonResponse
    {
        // Researcher or Admin triggers IRB PDF generation
        $ethicsRequest = $this->ethicsService->generateRequest($proposal, $request->user());
        return response()->json($ethicsRequest, 201);
    }

    public function markSubmitted(EthicsRequest $ethicsRequest): JsonResponse
    {
        $this->ethicsService->markAsSubmitted($ethicsRequest);
        return response()->json(['message' => 'Marked as submitted to IRB.']);
    }

    public function update(Request $request, EthicsRequest $ethicsRequest): JsonResponse
    {
        // Ethics Officer decides (Approve/Reject)
        $request->validate([
            'approval_status_id' => 'required|exists:ethics_approval_statuses,id',
            'comments' => 'nullable|string'
        ]);

        $ethicsRequest->update([
            'approval_status_id' => $request->approval_status_id,
            'comments' => $request->comments,
        ]);

        return response()->json($ethicsRequest);
    }
}
