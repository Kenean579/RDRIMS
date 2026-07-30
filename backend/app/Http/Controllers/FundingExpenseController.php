<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApproveFundingRequest;
use App\Http\Requests\StoreFundingExpenseRequest;
use App\Http\Resources\FundingExpenseResource;
use App\Models\Funding;
use App\Models\FundingExpense;
use App\Services\FundingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FundingExpenseController extends Controller
{
    public function __construct(
        private FundingService $fundingService,
    ) {}

    public function index(Request $request, Funding $funding): JsonResponse
    {
        $this->authorize('view', $funding);

        $expenses = $funding->expenses()
            ->with('budgetCategory', 'expenseCategory', 'submittedBy', 'approvedBy')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('expense_date', 'desc')
            ->paginate(20);

        return response()->json(FundingExpenseResource::collection($expenses));
    }

    public function store(StoreFundingExpenseRequest $request, Funding $funding): JsonResponse
    {
        $this->authorize('view', $funding);

        $expense = $this->fundingService->recordExpense(
            $funding,
            $request->validated(),
            $request->user()->id
        );

        return response()->json(new FundingExpenseResource($expense), 201);
    }

    public function show(Funding $funding, FundingExpense $expense): JsonResponse
    {
        $this->authorize('view', $funding);
        $this->authorize('view', $expense);

        $expense->load([
            'budgetCategory',
            'expenseCategory',
            'submittedBy',
            'approvedBy',
        ]);

        return response()->json(new FundingExpenseResource($expense));
    }

    public function update(StoreFundingExpenseRequest $request, Funding $funding, FundingExpense $expense): JsonResponse
    {
        $this->authorize('view', $funding);
        $this->authorize('update', $expense);

        $expense->update($request->validated());

        return response()->json(new FundingExpenseResource($expense));
    }

    public function destroy(Funding $funding, FundingExpense $expense): JsonResponse
    {
        $this->authorize('view', $funding);
        $this->authorize('delete', $expense);

        $expense->delete();

        return response()->json(['message' => 'Expense deleted successfully.']);
    }

    public function approve(ApproveFundingRequest $request, Funding $funding, FundingExpense $expense): JsonResponse
    {
        $this->authorize('view', $funding);
        $this->authorize('approve', $expense);

        $expense = $this->fundingService->approveExpense(
            $expense,
            $request->user()->id,
            $request->comments
        );

        return response()->json(new FundingExpenseResource($expense));
    }

    public function reject(ApproveFundingRequest $request, Funding $funding, FundingExpense $expense): JsonResponse
    {
        $this->authorize('view', $funding);
        $this->authorize('reject', $expense);

        $expense = $this->fundingService->rejectExpense(
            $expense,
            $request->user()->id,
            $request->comments
        );

        return response()->json(new FundingExpenseResource($expense));
    }
}
