<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApproveExpenseRequest;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\Expense;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Project $project): JsonResponse
    {
        return response()->json($project->expenses()->with('approvedBy')->paginate(20));
    }

    public function store(StoreExpenseRequest $request, Project $project): JsonResponse
    {
        $expense = $project->expenses()->create($request->validated());
        return response()->json($expense, 201);
    }

    public function show(Expense $expense): JsonResponse
    {
        return response()->json($expense->load('project', 'approvedBy'));
    }

    public function update(UpdateExpenseRequest $request, Expense $expense): JsonResponse
    {
        $this->authorize('update', $expense);
        $expense->update($request->validated());
        return response()->json($expense);
    }

    public function approve(ApproveExpenseRequest $request, Expense $expense): JsonResponse
    {
        $expense->update([
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);
        return response()->json(['message' => 'Expense approved.']);
    }

    public function destroy(Expense $expense): JsonResponse
    {
        $this->authorize('delete', $expense);
        $expense->delete();
        return response()->json(['message' => 'Expense deleted.']);
    }
}