<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitReviewRequest;
use App\Models\Proposal;
use App\Models\ProposalReviewer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewerProposalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasRole('super_admin', 'research_admin')) {
            $assignments = ProposalReviewer::with(['proposal.status', 'proposal.type', 'reviewer'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            $transformed = $assignments->getCollection()->map(function ($pivot) {
                // Ensure proposal is returned but disguised as having the pivot explicitly named reviewPivot
                $proposal = $pivot->proposal;
                $proposal->reviewPivot = clone $pivot;
                // Avoid recursive loops
                unset($proposal->reviewPivot->proposal); 
                return $proposal;
            });
            $assignments->setCollection($transformed);

            return response()->json($assignments);
        }

        $proposals = $user->reviewedProposals()->with('status', 'type')->paginate(20);
        return response()->json($proposals);
    }

    public function show(Proposal $proposal, Request $request): JsonResponse
    {
        $isReviewer = $proposal->reviewers()->where('reviewer_id', $request->user()->id)->exists();

        if (! $isReviewer) {
            abort(403, 'You are not assigned as a reviewer for this proposal.');
        }

        // Load only necessary relations and hide submitter for blind review
        $proposal->load('status', 'type', 'file');
        $proposal->setRelation('submittedBy', null);
        $proposal->setRelation('investigators', collect([]));
        $proposal->submitted_by = null;

        return response()->json($proposal);
    }

    public function storeReview(SubmitReviewRequest $request, Proposal $proposal): JsonResponse
    {
        $reviewerId = $request->user()->id;
        $reviewer = $proposal->reviewers()->where('reviewer_id', $reviewerId)->first();

        if (!$reviewer) {
            abort(403, 'You are not assigned as a reviewer for this proposal.');
        }

        $pivot = ProposalReviewer::where('proposal_id', $proposal->id)
    ->where('reviewer_id', $reviewerId)
    ->first();

if (!$pivot) {
    abort(403, 'Reviewer assignment not found.');
}

        // Save scores per criterion
        foreach ($request->scores as $scoreData) {
            $pivot->scores()->create([
                'criterion_id' => $scoreData['criterion_id'],
                'score' => $scoreData['score'],
                'comments' => $scoreData['comments'] ?? null,
            ]);
        }

        // Update pivot with overall review
        $proposal->reviewers()->updateExistingPivot($reviewerId, [
            'overall_score' => $request->overall_score,
            'overall_comments' => $request->overall_comments,
            'decision_id' => $request->decision_id,
            'submitted_at' => now(),
        ]);

        return response()->json(['message' => 'Review submitted.']);
    }
}
