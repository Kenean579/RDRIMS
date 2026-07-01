<?php

namespace App\Http\Controllers;

use App\Models\FinanceCheck;
use App\Models\Proposal;
use App\Services\FinanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceCheckController extends Controller
{
    public function __construct(private FinanceService $financeService) {}

    public function index(Request $request): JsonResponse
    {
        $checks = FinanceCheck::with(['proposal', 'status', 'checker'])
            ->whereHas('proposal', fn($q) => $q->hierarchical($request->user(), 'submitted_by'))
            ->latest()
            ->paginate(20);
        return response()->json($checks);
    }

    public function store(Request $request, Proposal $proposal): JsonResponse
    {
        // Admin sends proposal to Finance Check
        $check = $this->financeService->createCheck($proposal, $request->user());

        $proposalStatusId = \App\Models\ProposalStatus::where('name', 'finance_check')->value('id');
        if ($proposalStatusId) {
            $proposal->update(['status_id' => $proposalStatusId]);
        }

        return response()->json($check, 201);
    }

    public function update(Request $request, FinanceCheck $financeCheck): JsonResponse
    {
        $statusInput = $request->input('status');
        $statusId = null;

        if ($statusInput) {
            $statusId = \App\Models\FinanceCheckStatus::where('name', $statusInput)->value('id');
        } else {
            $statusId = $request->input('status_id');
        }

        $request->merge(['status_id' => $statusId]);

        // Finance Officer evaluates (Approve/Reject)
        $request->validate([
            'status_id' => 'required|exists:finance_check_statuses,id',
            'comments' => 'nullable|string'
        ]);

        $financeCheck->update([
            'status_id' => $request->status_id,
            'checker_id' => $request->user()->id,
            'checked_at' => now(),
            'comments' => $request->comments,
        ]);

        return response()->json($financeCheck);
    }
}
