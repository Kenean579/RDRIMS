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
        
        $data = $request->validated();
        if (empty($data['description'])) {
            $data['description'] = $data['title'] ?? '';
        }
        if (empty($data['due_date'])) {
            $data['due_date'] = $milestone->due_date ? $milestone->due_date->toDateString() : now()->toDateString();
        }
        if (empty($data['status_id'])) {
            $notStarted = \App\Models\TaskStatus::where('name', 'not_started')->first();
            $data['status_id'] = $notStarted ? $notStarted->id : (\App\Models\TaskStatus::first()?->id ?? 1);
        }

        $task = $milestone->tasks()->create($data);
        
        return response()->json(new TaskResource($task), 201);
    }

    public function storeStandalone(Request $request): JsonResponse
    {
        $request->validate([
            'milestone_id' => 'required|exists:milestones,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status_id' => 'nullable|integer',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
        ]);

        $milestone = Milestone::findOrFail($request->milestone_id);
        
        $this->authorize('create', [Task::class, $milestone]);
        
        $statusId = $request->status_id;
        if (!$statusId || !\App\Models\TaskStatus::where('id', $statusId)->exists()) {
            $notStarted = \App\Models\TaskStatus::where('name', 'not_started')->first();
            $statusId = $notStarted ? $notStarted->id : (\App\Models\TaskStatus::first()?->id ?? 1);
        }

        $task = $milestone->tasks()->create([
            'title' => $request->title,
            'description' => $request->description ?? $request->title,
            'status_id' => $statusId,
            'assigned_to' => $request->assigned_to,
            'due_date' => $request->due_date ?? ($milestone->due_date ? $milestone->due_date->toDateString() : now()->toDateString()),
        ]);
        
        return response()->json(new TaskResource($task), 201);
    }

    public function show(Milestone $milestone, Task $task): JsonResponse
    {
        $this->authorize('view', $task);
        
        $task->load('assignedTo', 'status', 'milestone.project');
        
        return response()->json(new TaskResource($task));
    }

    public function update(UpdateTaskRequest $request, ...$args): JsonResponse
    {
        $task = null;
        foreach ($args as $arg) {
            if ($arg instanceof Task) {
                $task = $arg;
            }
        }
        if (!$task) {
            $routeTask = $request->route('task');
            $task = $routeTask instanceof Task ? $routeTask : Task::findOrFail($routeTask);
        }

        $this->authorize('update', $task);

        $validated = $request->validated();

        if (!empty($validated['status'])) {
            $statusObj = \App\Models\TaskStatus::where('name', $validated['status'])->first();
            if ($statusObj) {
                $validated['status_id'] = $statusObj->id;
            }
            unset($validated['status']);
        } elseif (isset($validated['status_id'])) {
            if (!is_numeric($validated['status_id'])) {
                $statusObj = \App\Models\TaskStatus::where('name', $validated['status_id'])->first();
                if ($statusObj) {
                    $validated['status_id'] = $statusObj->id;
                }
            }
        }

        $task->update($validated);

        return response()->json(new TaskResource($task->fresh('status')));
    }

    public function destroy(...$args): JsonResponse
    {
        $task = null;
        foreach ($args as $arg) {
            if ($arg instanceof Task) {
                $task = $arg;
            }
        }
        if (!$task) {
            $routeTask = request()->route('task');
            $task = $routeTask instanceof Task ? $routeTask : Task::findOrFail($routeTask);
        }

        $this->authorize('delete', $task);

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully.']);
    }
}