<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Milestone;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Milestone $milestone): JsonResponse
    {
        return response()->json($milestone->tasks()->with('assignedTo', 'status')->get());
    }

    public function store(StoreTaskRequest $request, Milestone $milestone): JsonResponse
    {
        $task = $milestone->tasks()->create($request->validated());
        return response()->json($task, 201);
    }

    public function storeStandalone(Request $request): JsonResponse
    {
        $request->validate([
            'milestone_id' => 'required|exists:milestones,id',
            'title' => 'required|string|max:255',
            'status_id' => 'required|integer',
        ]);

        $milestone = Milestone::findOrFail($request->milestone_id);
        $task = $milestone->tasks()->create($request->only(['title', 'status_id']));
        return response()->json($task, 201);
    }

    public function show(Task $task): JsonResponse
    {
        return response()->json($task->load('assignedTo', 'status'));
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);
        $task->update($request->validated());
        return response()->json($task);
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);
        $task->delete();
        return response()->json(['message' => 'Task deleted.']);
    }
}