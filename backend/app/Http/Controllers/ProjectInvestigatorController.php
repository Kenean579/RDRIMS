<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddInvestigatorRequest;
use App\Http\Resources\ProjectInvestigatorResource;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProjectInvestigatorController extends Controller
{
    use AuthorizesRequests;

    public function index(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $investigators = $project->investigators()
            ->with('user.department')
            ->get();

        return response()->json(ProjectInvestigatorResource::collection($investigators));
    }

    public function store(AddInvestigatorRequest $request, Project $project): JsonResponse
    {
        $this->authorize('manageTeam', $project);

        $investigator = $project->investigators()->create([
            'user_id' => $request->user_id,
            'role' => $request->role,
        ]);

        return response()->json(new ProjectInvestigatorResource($investigator->load('user')), 201);
    }

    public function destroy(Project $project, int $investigatorId): JsonResponse
    {
        $this->authorize('manageTeam', $project);

        $project->investigators()->findOrFail($investigatorId)->delete();

        return response()->json(['message' => 'Investigator removed successfully.'], 200);
    }
}
