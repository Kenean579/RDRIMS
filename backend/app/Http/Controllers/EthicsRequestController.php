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

    /**
     * List ethics requests with tenant isolation
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();

        $query = EthicsRequest::with('proposal.submittedBy', 'approvalStatus', 'reviewer')
            ->when($request->status, fn($q) => $q->byStatus($request->status))
            ->when($request->search, fn($q) => $q->whereHas('proposal', fn($p) => $p->where('title', 'LIKE', '%' . $request->search . '%')));

        // Tenant isolation: scope by user's institution
        if ($user && !$user->hasRole('super_admin')) {
            $query->whereHas('proposal.submittedBy', fn($q) => $q->where('university_id', $user->university_id));
        }

        $ethics = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($ethics);
    }

    /**
     * Show a specific ethics request
     */
    public function show(EthicsRequest $ethicsRequest): JsonResponse
    {
        $this->authorize('view', $ethicsRequest);
        
        $ethicsRequest->load([
            'proposal.submittedBy',
            'approvalStatus',
            'reviewer',
            'createdBy',
            'updatedBy',
        ]);

        return response()->json($ethicsRequest);
    }

    /**
     * Create a new ethics request from a proposal
     */
    public function store(Request $request, Proposal $proposal): JsonResponse
    {
        $this->authorize('view', $proposal);
        $this->authorize('create', EthicsRequest::class);

        // Validate that reviewers have submitted their reviews
        $reviewers = $proposal->reviewers;
        if ($reviewers->isEmpty()) {
            return response()->json(
                ['message' => 'Reviewer assignment is incomplete. Please assign reviewers first.'],
                422
            );
        }

        $pendingReviews = $proposal->reviewers()->wherePivotNull('submitted_at')->count();
        if ($pendingReviews > 0) {
            return response()->json(
                ['message' => "Cannot generate Ethics request. There are still {$pendingReviews} pending peer reviews."],
                422
            );
        }

        // Generate ethics request
        $ethicsRequest = $this->ethicsService->generateRequest($proposal, $request->user());

        // Update proposal status to ethics_pending
        $proposalStatusId = \App\Models\ProposalStatus::where('name', 'ethics_pending')->value('id');
        if ($proposalStatusId) {
            $proposal->update(['status_id' => $proposalStatusId]);
        }

        return response()->json($ethicsRequest, 201);
    }

    /**
     * Update ethics request (resubmit after revision)
     */
    public function update(Request $request, EthicsRequest $ethicsRequest): JsonResponse
    {
        $this->authorize('update', $ethicsRequest);

        $request->validate([
            'comments' => 'nullable|string|max:1000',
        ]);

        $data = $request->only(['comments']);
        
        $ethicsRequest = $this->ethicsService->updateRequest($ethicsRequest, $data, $request->user());

        return response()->json($ethicsRequest);
    }

    /**
     * Delete ethics request
     */
    public function destroy(EthicsRequest $ethicsRequest): JsonResponse
    {
        $this->authorize('delete', $ethicsRequest);
        $ethicsRequest->delete();
        
        return response()->json(['message' => 'Ethics request deleted.']);
    }

    /**
     * Mark ethics request as submitted to IRB
     */
    public function markSubmitted(Request $request, EthicsRequest $ethicsRequest): JsonResponse
    {
        $this->authorize('markSubmitted', $ethicsRequest);
        
        $ethicsRequest = $this->ethicsService->markAsSubmitted($ethicsRequest, $request->user());
        
        return response()->json(['message' => 'Marked as submitted to IRB.', 'data' => $ethicsRequest]);
    }

    /**
     * Approve ethics request
     */
    public function approve(Request $request, EthicsRequest $ethicsRequest): JsonResponse
    {
        $this->authorize('approve', $ethicsRequest);

        $request->validate([
            'comments' => 'nullable|string|max:1000',
        ]);

        try {
            $ethicsRequest = $this->ethicsService->approve(
                $ethicsRequest,
                $request->user(),
                $request->comments
            );

            return response()->json(['message' => 'Ethics request approved.', 'data' => $ethicsRequest]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Reject ethics request
     */
    public function reject(Request $request, EthicsRequest $ethicsRequest): JsonResponse
    {
        $this->authorize('reject', $ethicsRequest);

        $request->validate([
            'comments' => 'required|string|max:1000',
        ]);

        try {
            $ethicsRequest = $this->ethicsService->reject(
                $ethicsRequest,
                $request->user(),
                $request->comments
            );

            return response()->json(['message' => 'Ethics request rejected.', 'data' => $ethicsRequest]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Request revision on ethics request
     */
    public function requestRevision(Request $request, EthicsRequest $ethicsRequest): JsonResponse
    {
        $this->authorize('requestRevision', $ethicsRequest);

        $request->validate([
            'comments' => 'required|string|max:1000',
        ]);

        try {
            $ethicsRequest = $this->ethicsService->requestRevision(
                $ethicsRequest,
                $request->user(),
                $request->comments
            );

            return response()->json(['message' => 'Revision requested.', 'data' => $ethicsRequest]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
