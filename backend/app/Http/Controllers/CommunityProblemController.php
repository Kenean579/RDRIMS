<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommunityProblemRequest;
use App\Http\Requests\UpdateCommunityProblemRequest;
use App\Http\Requests\CompleteCommunityProblemRequest;
use App\Http\Requests\FeedbackCommunityProblemRequest;
use App\Models\CommunityProblem;
use App\Models\CommunityProblemStatus;
use App\Services\CommunityProblemService;
use Illuminate\Http\JsonResponse;

class CommunityProblemController extends Controller
{
    public function __construct(private CommunityProblemService $problemService) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', CommunityProblem::class);

        $problems = CommunityProblem::with(['status', 'submittedBy', 'claimedBy', 'linkedProject'])
            ->latest()
            ->paginate(20);

        return response()->json($problems);
    }

    public function store(StoreCommunityProblemRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['submitted_by'] = $request->is_anonymous ? null : auth()->id();
        $data['status_id'] = CommunityProblemStatus::where('name', 'open')->first()->id;

        $problem = CommunityProblem::create($data);
        return response()->json($problem, 201);
    }

    public function show(CommunityProblem $problem): JsonResponse
    {
        $this->authorize('view', $problem);
        $problem->load(['status', 'submittedBy', 'claimedBy', 'linkedProject']);
        return response()->json($problem);
    }

    public function update(UpdateCommunityProblemRequest $request, CommunityProblem $problem): JsonResponse
    {
        $problem->update($request->validated());
        return response()->json($problem);
    }

    public function destroy(CommunityProblem $problem): JsonResponse
    {
        $this->authorize('delete', $problem);
        $problem->delete();
        return response()->json(null, 204);
    }

    public function claim(CommunityProblem $problem): JsonResponse
    {
        $this->authorize('claim', $problem);
        $this->problemService->claim($problem, auth()->id());
        return response()->json($problem);
    }

    public function complete(CompleteCommunityProblemRequest $request, CommunityProblem $problem): JsonResponse
    {
        $this->problemService->complete($problem);

        if ($request->has('linked_project_id')) {
            $problem->update(['linked_project_id' => $request->linked_project_id]);
        }

        return response()->json($problem);
    }

    public function addFeedback(FeedbackCommunityProblemRequest $request, CommunityProblem $problem): JsonResponse
    {
        $this->problemService->addFeedback($problem, $request->feedback, $request->rating);
        return response()->json($problem);
    }
}
