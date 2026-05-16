<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewCriterionRequest;
use App\Http\Requests\UpdateReviewCriterionRequest;
use App\Models\ReviewCriterion;
use Illuminate\Http\JsonResponse;

class ReviewCriterionController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(ReviewCriterion::all());
    }

    public function store(StoreReviewCriterionRequest $request): JsonResponse
    {
        $criterion = ReviewCriterion::create($request->validated());
        return response()->json($criterion, 201);
    }

    public function show(ReviewCriterion $reviewCriterion): JsonResponse
    {
        return response()->json($reviewCriterion);
    }

    public function update(UpdateReviewCriterionRequest $request, ReviewCriterion $reviewCriterion): JsonResponse
    {
        $reviewCriterion->update($request->validated());
        return response()->json($reviewCriterion);
    }

    public function destroy(ReviewCriterion $reviewCriterion): JsonResponse
    {
        $reviewCriterion->delete();
        return response()->json(['message' => 'Review criterion deleted.']);
    }
}
