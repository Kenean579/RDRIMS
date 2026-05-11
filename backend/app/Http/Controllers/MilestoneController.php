<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMilestoneRequest;
use App\Http\Requests\UpdateMilestoneRequest;
use App\Models\Milestone;
use App\Models\Project;
use Illuminate\Http\JsonResponse;

class MilestoneController extends Controller
{
    public function index(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $milestones = $project->milestones()
            ->with('status')
            ->orderBy('display_order')
            ->get();

        return response()->json($milestones);
    }

    public function store(StoreMilestoneRequest $request, Project $project): JsonResponse
    {
        $milestone = $project->milestones()->create($request->validated());
        return response()->json($milestone, 201);
    }

    public function show(Project $project, Milestone $milestone): JsonResponse
    {
        $this->authorize('view', $project);
        $milestone->load(['status', 'tasks']);
        return response()->json($milestone);
    }

    public function update(UpdateMilestoneRequest $request, Project $project, Milestone $milestone): JsonResponse
    {
        $milestone->update($request->validated());
        return response()->json($milestone);
    }

    public function destroy(Project $project, Milestone $milestone): JsonResponse
    {
        $this->authorize('update', $project);
        $milestone->delete();
        return response()->json(null, 204);
    }
}