<?php

namespace App\Http\Controllers;

use App\Models\EthicsRequest;
use App\Models\Proposal;
use App\Services\EthicsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EthicsRequestController extends Controller
{
    public function __construct(private EthicsService $ethicsService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json(
            EthicsRequest::with('proposal', 'approvalStatus')
                ->whereHas('proposal', fn($q) => $q->hierarchical($request->user(), 'submitted_by'))
                ->latest()
                ->paginate(20)
        );
    }

    public function show(EthicsRequest $ethicsRequest): JsonResponse
    {
        $this->authorize('view', $ethicsRequest);
        $ethicsRequest->load([
            'proposal.submittedBy',
            'proposal.thematicArea',
            'proposal.files',
            'approvalStatus',
            'reviewer',
        ]);

        return response()->json($ethicsRequest);
    }

    public function store(Request $request, Proposal $proposal): JsonResponse
    {
        $this->authorize('view', $proposal);
        $reviewers = $proposal->reviewers;
        if ($reviewers->isEmpty()) {
            return response()->json(['message' => 'Reviewer assignment is incomplete. Please assign reviewers first.'], 422);
        }
        $pendingReviews = $proposal->reviewers()->wherePivotNull('submitted_at')->count();
        if ($pendingReviews > 0) {
            return response()->json(['message' => "Cannot generate Ethics request. There are still {$pendingReviews} pending peer reviews."], 422);
        }

        // Researcher or Admin triggers IRB PDF generation
        $ethicsRequest = $this->ethicsService->generateRequest($proposal, $request->user());

        $proposalStatusId = \App\Models\ProposalStatus::where('name', 'ethics_pending')->value('id');
        if ($proposalStatusId) {
            $proposal->update(['status_id' => $proposalStatusId]);
        }

        return response()->json($ethicsRequest, 201);
    }

    public function markSubmitted(EthicsRequest $ethicsRequest): JsonResponse
    {
        $this->authorize('update', $ethicsRequest);
        $this->ethicsService->markAsSubmitted($ethicsRequest);
        return response()->json(['message' => 'Marked as submitted to IRB.']);
    }

    public function update(Request $request, EthicsRequest $ethicsRequest): JsonResponse
    {
        $this->authorize('update', $ethicsRequest);

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

    public function decision(Request $request, EthicsRequest $ethicsRequest): JsonResponse
    {
        $this->authorize('update', $ethicsRequest);

        $request->validate([
            'status' => 'required|in:approved,needs_revision,rejected',
            'note' => 'nullable|string'
        ]);

        $this->ethicsService->makeDecision($ethicsRequest, $request->status, $request->user(), $request->note);

        return response()->json(['message' => 'Decision recorded successfully', 'data' => $ethicsRequest->fresh(['approvalStatus', 'proposal'])]);
    }
}
