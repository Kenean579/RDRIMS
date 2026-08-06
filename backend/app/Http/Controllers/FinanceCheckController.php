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
        $checks = FinanceCheck::with(['proposal.submittedBy', 'status', 'checker'])
            ->whereHas('proposal', fn($q) => $q->hierarchical($request->user(), 'submitted_by'))
            ->when($request->status, fn($q) => $q->whereHas('status', fn($sq) => $sq->where('name', $request->status)))
            ->latest()
            ->paginate(20);
        return response()->json($checks);
    }

    public function store(Request $request, Proposal $proposal): JsonResponse
    {
        $this->authorize('view', $proposal);
        // 1. All peer reviews must be completed first
        $reviewers = $proposal->reviewers;
        if ($reviewers->isEmpty()) {
            return response()->json(['message' => 'Reviewer assignment is incomplete. Please assign reviewers first.'], 422);
        }
        $pendingReviews = $proposal->reviewers()->wherePivotNull('submitted_at')->count();
        if ($pendingReviews > 0) {
            return response()->json(['message' => "Cannot request Finance check. There are still {$pendingReviews} pending peer reviews."], 422);
        }

        // 2. Ethics approval must be completed first (if required)
        $ethicsRequired = \App\Models\Setting::where('key', 'ethics_required')->value('value') === 'true';
        if ($ethicsRequired) {
            $latestEthicsRequest = $proposal->ethicsRequests()->latest()->first();
            if (!$latestEthicsRequest) {
                return response()->json(['message' => 'Ethics clearance must be requested and approved before Finance check.'], 422);
            }
            $approvedStatusId = \App\Models\EthicsApprovalStatus::where('name', 'approved')->value('id');
            if ($latestEthicsRequest->approval_status_id !== $approvedStatusId) {
                return response()->json(['message' => 'Ethics clearance is not yet approved. Finance check cannot proceed.'], 422);
            }
        }

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
        $this->authorize('update', $financeCheck);
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

    public function approve(Request $request, FinanceCheck $financeCheck): JsonResponse
    {
        $this->authorize('update', $financeCheck);

        $validated = $request->validate([
            'comments' => 'nullable|string|max:1000',
        ]);

        $this->financeService->approve(
            $financeCheck,
            $request->user(),
            $validated['comments'] ?? null
        );

        return response()->json([
            'message' => 'Finance check approved.',
            'data' => $financeCheck->fresh(['proposal', 'status', 'checker']),
        ]);
    }

    public function reject(Request $request, FinanceCheck $financeCheck): JsonResponse
    {
        $this->authorize('update', $financeCheck);

        $validated = $request->validate([
            'comments' => 'required|string|max:1000',
        ]);

        $this->financeService->reject(
            $financeCheck,
            $request->user(),
            $validated['comments']
        );

        return response()->json([
            'message' => 'Finance check rejected.',
            'data' => $financeCheck->fresh(['proposal', 'status', 'checker']),
        ]);
    }

    public function show(FinanceCheck $financeCheck): JsonResponse
    {
        $this->authorize('view', $financeCheck);

        return response()->json($financeCheck->load(['proposal', 'status', 'checker']));
    }
}
