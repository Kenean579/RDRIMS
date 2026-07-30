<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Milestone;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Milestone $milestone): JsonResponse
    {
        $this->authorize('view', $milestone);
        
        $tasks = $milestone->tasks()
            ->with('assignedTo', 'status')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json(TaskResource::collection($tasks));
    }

    public function store(StoreTaskRequest $request, Milestone $milestone): JsonResponse
    {
        $this->authorize('create', [Task::class, $milestone]);
        
        $task = $milestone->tasks()->create($request->validated());
        
        return response()->json(new TaskResource($task), 201);
    }

    public function storeStandalone(Request $request): JsonResponse
    {
        $request->validate([
            'milestone_id' => 'required|exists:milestones,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status_id' => 'required|integer|exists:task_statuses,id',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
        ]);

        $milestone = Milestone::findOrFail($request->milestone_id);
        
        $this->authorize('create', [Task::class, $milestone]);
        
        $task = $milestone->tasks()->create($request->only([
            'title', 
            'description', 
            'status_id', 
            'assigned_to', 
            'due_date'
        ]));
        
        return response()->json(new TaskResource($task), 201);
    }

    public function show(Milestone $milestone, Task $task): JsonResponse
    {
        $this->authorize('view', $task);
        
        $task->load('assignedTo', 'status', 'milestone.project');
        
        return response()->json(new TaskResource($task));
    }

    public function update(UpdateTaskRequest $request, Milestone $milestone, Task $task): JsonResponse
    {
        $this->authorize('update', $task);
        
        $task->update($request->validated());
        
        return response()->json(new TaskResource($task));
    }

    public function destroy(Milestone $milestone, Task $task): JsonResponse
    {
        $this->authorize('delete', $task);
        
        $task->delete();
        
        return response()->json(['message' => 'Task deleted successfully.']);
    }
}