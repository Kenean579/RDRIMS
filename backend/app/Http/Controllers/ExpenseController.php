<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApproveExpenseRequest;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Project $project): JsonResponse
    {
        $this->authorize('viewFinancials', $project);
        
        $expenses = $project->expenses()
            ->with('approvedBy')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return response()->json(ExpenseResource::collection($expenses));
    }

    public function store(StoreExpenseRequest $request, Project $project): JsonResponse
    {
        $this->authorize('create', [Expense::class, $project]);
        
        $expense = $project->expenses()->create($request->validated());
        
        return response()->json(new ExpenseResource($expense), 201);
    }

    public function show(Expense $expense): JsonResponse
    {
        $this->authorize('view', $expense);
        
        $expense->load('project', 'approvedBy');
        
        return response()->json(new ExpenseResource($expense));
    }

    public function update(UpdateExpenseRequest $request, Project $project, Expense $expense): JsonResponse
    {
        $this->authorize('update', $expense);
        
        $expense->update($request->validated());
        
        return response()->json(new ExpenseResource($expense));
    }

    public function approve(ApproveExpenseRequest $request, Project $project, Expense $expense): JsonResponse
    {
        $this->authorize('approve', $expense);
        
        $expense->update([
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);
        
        return response()->json([
            'message' => 'Expense approved successfully.',
            'expense' => new ExpenseResource($expense),
        ]);
    }

    public function destroy(Project $project, Expense $expense): JsonResponse
    {
        $this->authorize('delete', $expense);
        
        $expense->delete();
        
        return response()->json(['message' => 'Expense deleted successfully.']);
    }
}