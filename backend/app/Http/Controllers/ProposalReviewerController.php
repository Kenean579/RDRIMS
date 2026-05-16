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
            $proposal->reviewers()->attach($reviewerId, [
                'assigned_by' => $request->user()->id,
                'assigned_at' => now(),
            ]);
        }

        return response()->json(['message' => 'Reviewers assigned.']);
    }

    public function destroy(Proposal $proposal, int $reviewerId): JsonResponse
    {
        $proposal->reviewers()->detach($reviewerId);
        return response()->json(['message' => 'Reviewer removed.']);
    }
}
