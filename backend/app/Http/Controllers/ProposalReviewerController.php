<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignReviewersRequest;
use App\Models\Proposal;
use Illuminate\Http\JsonResponse;

class ProposalReviewerController extends Controller
{
    public function index(Proposal $proposal): JsonResponse
    {
        return response()->json($proposal->reviewers);
    }

    public function store(AssignReviewersRequest $request, Proposal $proposal): JsonResponse
    {
        foreach ($request->reviewer_ids as $reviewerId) {
            // Prevent duplicate assignments
            if (!$proposal->reviewers()->where('reviewer_id', $reviewerId)->exists()) {
                $proposal->reviewers()->attach($reviewerId, [
                    'assigned_by' => $request->user()->id,
                    'assigned_at' => now(),
                ]);
            }
        }

        return response()->json(['message' => 'Reviewers assigned.']);
    }

    public function destroy(Proposal $proposal, int $reviewerId): JsonResponse
    {
        $proposal->reviewers()->detach($reviewerId);
        return response()->json(['message' => 'Reviewer removed.']);
    }

    public function recommendations(Proposal $proposal): JsonResponse
    {
        $submitter = $proposal->submittedBy;
        $proposalKeywords = array_map('trim', explode(',', strtolower($proposal->keywords ?? '')));

        $reviewers = \App\Models\User::whereHas('roles', function($q) {
                $q->where('name', 'reviewer');
            })
            ->where('id', '!=', $submitter->id)
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
}
