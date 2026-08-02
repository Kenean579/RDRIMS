<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommunityProblemRequest;
use App\Http\Requests\UpdateCommunityProblemRequest;
use App\Models\CommunityProblem;
use App\Services\CommunityProblemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommunityProblemController extends Controller
{
    public function __construct(
        private CommunityProblemService $communityProblemService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $problems = CommunityProblem::with('status', 'submittedBy', 'claimedBy', 'linkedProject')
            ->when($request->status, fn($q) => $q->whereHas('status', fn($s) => $s->where('name', $request->status)))
            ->when($request->location, fn($q) => $q->where('location', 'LIKE', '%' . $request->location . '%'))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($problems);
    }

    public function store(StoreCommunityProblemRequest $request): JsonResponse
    {
        $data = $request->safe()->except(['status_id', 'linked_project_id']);

        $problem = CommunityProblem::create([
            ...$data,
            'submitted_by' => auth('sanctum')->user()?->id,
            'status_id' => CommunityProblem::getStatusId('open'),
        ]);

        return response()->json($problem, 201);
    }

    public function show(CommunityProblem $communityProblem): JsonResponse
    {
        return response()->json($communityProblem->load('status', 'submittedBy', 'claimedBy', 'linkedProject'));
    }

    public function update(UpdateCommunityProblemRequest $request, CommunityProblem $communityProblem): JsonResponse
    {
        $this->authorize('update', $communityProblem);
        $communityProblem->update($request->validated());
        return response()->json($communityProblem);
    }

    public function claim(CommunityProblem $communityProblem, Request $request): JsonResponse
    {
        $this->communityProblemService->claim($communityProblem, $request->user()->id);
        return response()->json(['message' => 'Problem claimed.']);
    }

    public function complete(CommunityProblem $communityProblem, Request $request): JsonResponse
    {
        $this->communityProblemService->complete($communityProblem, $request->user()->id);
        return response()->json(['message' => 'Problem marked as completed.']);
    }

    public function addFeedback(Request $request, CommunityProblem $communityProblem): JsonResponse
    {
        $request->validate([
            'feedback' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $this->communityProblemService->addFeedback($communityProblem, $request->feedback, $request->rating);
        return response()->json(['message' => 'Results summary added.']);
    }

    public function destroy(CommunityProblem $communityProblem): JsonResponse
    {
        $this->authorize('delete', $communityProblem);
        $communityProblem->delete();
        return response()->json(['message' => 'Community problem deleted.']);
    }
}
