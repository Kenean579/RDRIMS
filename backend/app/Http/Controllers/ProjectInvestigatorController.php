<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProjectInvestigatorController extends Controller
{
    use AuthorizesRequests;

    public function index(Project $project): JsonResponse
    {
        $this->authorize('view', $project);
        return response()->json($project->investigators()->with('user')->get());
    }

    public function store(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|string|in:pi,co_pi,member',
        ]);

        $investigator = $project->investigators()->create($request->all());
        return response()->json($investigator, 201);
    }

    public function destroy(Project $project, int $investigatorId): JsonResponse
    {
        $this->authorize('update', $project);
        $project->investigators()->findOrFail($investigatorId)->delete();
        return response()->json(null, 204);
    }
}
