<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignReviewersRequest;
use App\Http\Resources\ProposalReviewerResource;
use App\Models\Proposal;
use App\Models\ProposalReviewer;
use App\Models\User;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProposalReviewerController extends Controller
{
    public function __construct(
        private ReviewService $reviewService
    ) {}
    public function index(Proposal $proposal): JsonResponse
    {
        $this->authorize('view', $proposal);
        
        // SECURITY: Verify tenant scope
        if (!$this->authorizedForProposal($proposal)) {
            abort(403, 'Unauthorized access to this proposal');
        }

        $reviewers = $proposal->reviewers()->with(['scores', 'decision'])->get();
        
        return response()->json([
            'reviewers' => ProposalReviewerResource::collection($reviewers),
        ]);
    }

    public function store(AssignReviewersRequest $request, Proposal $proposal): JsonResponse
    {
        $this->authorize('assignReviewers', $proposal);
        
        // SECURITY: Verify tenant scope
        if (!$this->authorizedForProposal($proposal)) {
            abort(403, 'Unauthorized access to this proposal');
        }

        $eligibleReviewerIds = $this->getEligibleReviewerIds($proposal, $request->user());
        $assignedCount = 0;
        
        foreach ($request->reviewer_ids as $reviewerId) {
            if (! in_array((int) $reviewerId, $eligibleReviewerIds, true)) {
                abort(422, "Reviewer {$reviewerId} is outside the proposal tenant scope.");
            }

            // Prevent duplicate assignments
            if (!$proposal->reviewers()->where('reviewer_id', $reviewerId)->exists()) {
                $proposal->reviewers()->attach($reviewerId, [
                    'assigned_by' => $request->user()->id,
                    'assigned_at' => now(),
                ]);
                $assignedCount++;
            }
        }

        return response()->json([
            'message' => 'Reviewers assigned.',
            'assigned_count' => $assignedCount,
        ]);
    }

    public function destroy(Proposal $proposal, int $reviewerId): JsonResponse
    {
        $this->authorize('assignReviewers', $proposal);
        
        // SECURITY: Verify tenant scope
        if (!$this->authorizedForProposal($proposal)) {
            abort(403, 'Unauthorized access to this proposal');
        }

        // Verify the reviewer is actually assigned
        $assignment = $proposal->reviewers()->where('reviewer_id', $reviewerId)->first();
        if (!$assignment) {
            abort(404, 'Reviewer not assigned to this proposal');
        }

        $proposal->reviewers()->detach($reviewerId);
        return response()->json(['message' => 'Reviewer removed.']);
    }

    public function recommendations(Proposal $proposal): JsonResponse
    {
        $this->authorize('view', $proposal);
        $submitter = $proposal->submittedBy;
        $proposalKeywords = array_map('trim', explode(',', strtolower($proposal->keywords ?? '')));

        // PERFORMANCE FIX: Eager load relationships to prevent N+1 queries
        $reviewers = User::whereHas('roles', function($q) {
                $q->where('name', 'reviewer');
            })
            ->hierarchical(request()->user(), 'id')
            ->where('id', '!=', $submitter->id)
            ->with('department')  // Prevent N+1
            ->withCount(['reviewedProposals as workload' => function($q) {
                // Count active review assignments
                $q->whereNull('proposal_reviewers.submitted_at');
            }])
            ->get();

        $recommended = $reviewers->map(function ($reviewer) use ($submitter, $proposalKeywords) {
            $score = 0;

            // 1. Conflict of interest
            $conflict = false;
            if ($reviewer->department_id && $submitter->department_id && $reviewer->department_id === $submitter->department_id) {
                $conflict = true;
                $score -= 100; // Penalize heavy for conflict
            }

            // 2. Keyword matching
            $reviewerKeywords = array_map('trim', explode(',', strtolower($reviewer->expertise_keywords ?? '')));
            $matches = array_intersect($proposalKeywords, $reviewerKeywords);
            $score += count($matches) * 10;

            // 3. Workload penalty
            $score -= $reviewer->workload * 2;

            return [
                'id' => $reviewer->id,
                'name' => $reviewer->name,
                'email' => $reviewer->email,
                'department' => $reviewer->department?->name,
                'expertise_keywords' => $reviewer->expertise_keywords,
                'workload' => $reviewer->workload,
                'conflict' => $conflict,
                'relevance_score' => max(0, $score), // Prevent negative scores in UI, though internally handled
                'matched_keywords' => $matches,
            ];
        })->sortByDesc('relevance_score')->values();

        return response()->json([
            'message' => $recommended->isEmpty() ? 'No reviewer with the required expertise is currently registered.' : 'Reviewers recommended based on expertise.',
            'recommendations' => $recommended
        ]);
    }

    public function reopen(Request $request, Proposal $proposal, int $reviewer): JsonResponse
    {
        $this->authorize('assignReviewers', $proposal);
        $pivot = ProposalReviewer::where('proposal_id', $proposal->id)
            ->where('reviewer_id', $reviewer)
            ->firstOrFail();

        $pivot = $this->reviewService->reopenReview($pivot, $request->user());

        return response()->json([
            'message' => 'Review reopened for revision.',
            'review' => $pivot,
        ]);
    }

    private function getEligibleReviewerIds(Proposal $proposal, User $actor): array
    {
        $investigatorIds = $proposal->investigators()->whereNotNull('user_id')->pluck('user_id');

        return User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'reviewer'))
            ->hierarchical($actor, 'id')
            ->where('id', '!=', $proposal->submitted_by)
            ->whereNotIn('id', $investigatorIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * SECURITY: Verify user has access to this proposal in their tenant scope.
     */
    private function authorizedForProposal(Proposal $proposal): bool
    {
        $user = request()->user();
        
        if (!$user) {
            return false;
        }

        // Super admin sees all
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // User must share institution with submitter
        $submittedBy = $proposal->relationLoaded('submittedBy')
            ? $proposal->getRelation('submittedBy')
            : $proposal->submittedBy;

        if (!$submittedBy instanceof User) {
            return false;
        }

        return $user->sharesInstitutionWith($submittedBy) || 
               $proposal->reviewers()->where('reviewer_id', $user->id)->exists();
    }
}
