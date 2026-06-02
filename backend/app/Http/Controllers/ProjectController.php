<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Models\Proposal;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(
        private ProjectService $projectService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $projects = Project::with('status', 'pi', 'academicYear')
            ->hierarchical($request->user(), 'pi_id')
            ->when($request->status, fn($q) => $q->whereHas('status', fn($s) => $s->where('name', $request->status)))
            ->when($request->search, fn($q) => $q->where('title', 'LIKE', '%' . $request->search . '%'))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($projects);
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = Project::create($request->validated());
        return response()->json($project, 201);
    }

    public function show(Project $project): JsonResponse
    {
        return response()->json($project->load('status', 'pi', 'investigators.user', 'milestones.tasks', 'expenses', 'publications', 'patents', 'outputs'));
    }

    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);
        $project->update($request->validated());
        return response()->json($project);
    }

    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('delete', $project);
        $project->delete();
        return response()->json(['message' => 'Project deleted.']);
    }

    public function createFromProposal(Proposal $proposal, Request $request): JsonResponse
    {
        $project = $this->projectService->createFromProposal($proposal, $request->user());
        return response()->json($project, 201);
    }

    public function changeStatus(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $request->validate([
            'status' => 'required|string|in:active,completed,suspended',
        ]);

        $statusMap = [
            'active' => 1,
            'completed' => 2,
            'suspended' => 3,
        ];

        $project->update(['status_id' => $statusMap[$request->status]]);

        return response()->json($project->load('status'));
    }
}