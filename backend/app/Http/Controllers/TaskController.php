<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Milestone;
use App\Models\Task;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
    public function index(Milestone $milestone): JsonResponse
    {
        $this->authorize('view', $milestone->project);

        $tasks = $milestone->tasks()
            ->with(['status', 'assignedTo'])
            ->orderBy('due_date')
            ->get();

        return response()->json($tasks);
    }

    public function store(StoreTaskRequest $request, Milestone $milestone): JsonResponse
    {
        $task = $milestone->tasks()->create($request->validated());
        return response()->json($task, 201);
    }

    public function show(Milestone $milestone, Task $task): JsonResponse
    {
        $this->authorize('view', $milestone->project);
        $task->load(['status', 'assignedTo']);
        return response()->json($task);
    }

    public function update(UpdateTaskRequest $request, Milestone $milestone, Task $task): JsonResponse
    {
        $task->update($request->validated());
        return response()->json($task);
    }

    public function destroy(Milestone $milestone, Task $task): JsonResponse
    {
        $this->authorize('update', $milestone->project);
        $task->delete();
        return response()->json(null, 204);
    }
}