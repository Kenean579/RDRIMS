<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Proposal;
use App\Http\Requests\ReviewRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ReviewController extends Controller
{
    use AuthorizesRequests;

    public function index(Proposal $proposal): JsonResponse
    {
        $this->authorize('view', $proposal);
        return response()->json($proposal->reviews()->with('reviewer')->get());
    }

    public function store(ReviewRequest $request, Proposal $proposal): JsonResponse
    {
        $this->authorize('review', $proposal);

        $review = $proposal->reviews()->create([
            'reviewer_id' => $request->user()->id,
            'general_comment' => $request->general_comment,
            'recommendation' => $request->recommendation,
            'review_date' => now(),
        ]);

        foreach ($request->scores as $scoreData) {
            $review->scores()->create($scoreData);
        }

        return response()->json($review->load('scores'), 201);
    }

    public function show(Review $review): JsonResponse
    {
        $this->authorize('view', $review->proposal);
        return response()->json($review->load('scores.criterion', 'reviewer'));
    }
}
