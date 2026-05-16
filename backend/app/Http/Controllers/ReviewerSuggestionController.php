<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use App\Services\ReviewerSuggestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ReviewerSuggestionController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private ReviewerSuggestionService $suggestionService) {}

    public function index(Proposal $proposal): JsonResponse
    {
        $this->authorize('assignReviewers', $proposal);
        $suggestions = $this->suggestionService->suggest($proposal);
        return response()->json($suggestions);
    }
}
