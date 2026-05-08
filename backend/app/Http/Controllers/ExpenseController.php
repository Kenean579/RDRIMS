<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\Expense;
use App\Models\Project;
use Illuminate\Http\JsonResponse;

class ExpenseController extends Controller
{
    public function index(Project $project): JsonResponse
    {
        $this->authorize('view', $project);
        $expenses = $project->expenses()->with('approvedBy')->latest()->paginate(20);
        return response()->json($expenses);
    }

    public function store(StoreExpenseRequest $request, Project $project): JsonResponse
    {
        $expense = $project->expenses()->create($request->validated());
        return response()->json($expense, 201);
    }

    public function show(Project $project, Expense $expense): JsonResponse
    {
        $this->authorize('view', $project);
        $expense->load('approvedBy');
        return response()->json($expense);
    }

    public function update(UpdateExpenseRequest $request, Project $project, Expense $expense): JsonResponse
    {
        $expense->update($request->validated());
        return response()->json($expense);
    }

    public function destroy(Project $project, Expense $expense): JsonResponse
    {
        $this->authorize('delete', $expense);
        $expense->delete();
        return response()->json(null, 204);
    }

    public function approve(Expense $expense): JsonResponse
    {
        $this->authorize('approve', $expense);
        $expense->update(['approved_by' => auth()->id(), 'approved_at' => now()]);
        return response()->json($expense);
    }
}