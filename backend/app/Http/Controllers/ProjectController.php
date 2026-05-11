<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Models\Proposal;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{
    public function __construct(private ProjectService $projectService) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Project::class);

        $projects = Project::with(['pi', 'status', 'academicYear'])
            ->when(!auth()->user()->roles()->where('name', 'admin')->exists(), function ($q) {
                $q->where('pi_id', auth()->id());
            })
            ->latest()
            ->paginate(20);

        return response()->json($projects);
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        if ($request->has('proposal_id')) {
            $proposal = Proposal::findOrFail($request->proposal_id);
            $project = $this->projectService->createFromProposal($proposal, $request->pi_id);
        } else {
            $project = Project::create($request->validated());
        }

        return response()->json($project, 201);
    }

    public function show(Project $project): JsonResponse
    {
        $this->authorize('view', $project);
        $project->load(['pi', 'status', 'milestones', 'expenses', 'publications']);
        return response()->json($project);
    }

    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $project->update($request->validated());
        return response()->json($project);
    }

    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('delete', $project);
        $project->delete();
        return response()->json(null, 204);
    }
}