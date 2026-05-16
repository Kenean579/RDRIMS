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
        return response()->json($project->milestones()->with('status', 'tasks')->orderBy('display_order')->get());
    }

    public function store(StoreMilestoneRequest $request, Project $project): JsonResponse
    {
        $milestone = $project->milestones()->create($request->validated());
        return response()->json($milestone, 201);
    }

    public function show(Milestone $milestone): JsonResponse
    {
        return response()->json($milestone->load('tasks'));
    }

    public function update(UpdateMilestoneRequest $request, Milestone $milestone): JsonResponse
    {
        $milestone->update($request->validated());
        return response()->json($milestone);
    }

    public function destroy(Milestone $milestone): JsonResponse
    {
        $milestone->delete();
        return response()->json(['message' => 'Milestone deleted.']);
    }
}