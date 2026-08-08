<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMilestoneRequest;
use App\Http\Requests\UpdateMilestoneRequest;
use App\Http\Resources\MilestoneResource;
use App\Models\Milestone;
use App\Models\Project;
use Illuminate\Http\JsonResponse;

class MilestoneController extends Controller
{
    public function index(Project $project): JsonResponse
    {
        $this->authorize('view', $project);
        
        $milestones = $project->milestones()
            ->with('status', 'tasks.status', 'tasks.assignedTo')
            ->orderBy('display_order')
            ->get();
            
        return response()->json(MilestoneResource::collection($milestones));
    }

    public function store(StoreMilestoneRequest $request, Project $project): JsonResponse
    {
        $this->authorize('create', [Milestone::class, $project]);
        
        $data = $request->validated();
        if (empty($data['status_id'])) {
            $pendingStatus = \App\Models\MilestoneStatus::where('name', 'pending')->first();
            $data['status_id'] = $pendingStatus ? $pendingStatus->id : (\App\Models\MilestoneStatus::first()?->id ?? 1);
        }

        $milestone = $project->milestones()->create($data);
        
        return response()->json(new MilestoneResource($milestone), 201);
    }

    public function show(Milestone $milestone): JsonResponse
    {
        $this->authorize('view', $milestone);
        
        $milestone->load('tasks.status', 'tasks.assignedTo', 'status');
        
        return response()->json(new MilestoneResource($milestone));
    }

    public function update(UpdateMilestoneRequest $request, Project $project, Milestone $milestone): JsonResponse
    {
        $this->authorize('update', $milestone);
        
        $milestone->update($request->validated());
        
        return response()->json(new MilestoneResource($milestone));
    }

    public function destroy(Project $project, Milestone $milestone): JsonResponse
    {
        $this->authorize('delete', $milestone);
        
        $milestone->delete();
        
        return response()->json(['message' => 'Milestone deleted successfully.']);
    }
}