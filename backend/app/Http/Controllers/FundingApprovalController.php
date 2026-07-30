<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApproveFundingRequest;
use App\Http\Resources\FundingApprovalResource;
use App\Models\Funding;
use App\Models\FundingApproval;
use App\Models\FundingExpense;
use App\Services\FundingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * FundingApprovalController
 * 
 * Handles approval workflow for Funding and Expenses.
 * 
 * Approval Workflow:
 * 1. Funding: draft -> submitted -> under_review -> approved/rejected
 * 2. Expense: pending -> approved/rejected
 * 
 * Security Features:
 * - Permission-based authorization
 * - Only designated approvers can approve
 * - Audit logging on all approvals
 */
class FundingApprovalController extends Controller
{
    protected FundingService $fundingService;

    public function __construct(FundingService $fundingService)
    {
        $this->fundingService = $fundingService;
    }

    /**
     * List pending approvals for the current user
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $this->authorize('viewApprovals', Funding::class);

        $query = FundingApproval::where('approver_id', $user->id)
            ->where('decision', null)
            ->with('funding', 'approver');

        // Filter by approval type
        $query->when($request->filled('type'), fn($q) =>
            $q->where('approval_type', $request->input('type'))
        );

        // Filter by current status
        $query->when($request->filled('status'), fn($q) =>
            $q->where('current_status', $request->input('status'))
        );

        return response()->json(
            FundingApprovalResource::collection(
                $query->orderBy('created_at', 'asc')->paginate(20)
            )
        );
    }

    /**
     * Get a specific approval record
     */
    public function show(FundingApproval $approval): JsonResponse
    {
        $this->authorize('viewApproval', $approval);

        $approval->load('funding', 'approver');

        return response()->json(
            FundingApprovalResource::make($approval)
        );
    }

    /**
     * Approve a funding or expense
     */
    public function approve(FundingApproval $approval, ApproveFundingRequest $request): JsonResponse
    {
        $this->authorize('approve', $approval);

        $user = $request->user();

        // Verify user is the designated approver
        if ($approval->approver_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized: You are not the designated approver for this item.',
            ], 403);
        }

        // Verify decision is not already made
        if ($approval->decision !== null) {
            return response()->json([
                'message' => 'This approval has already been decided.',
            ], 422);
        }

        $validated = $request->validated();
        $decision = $validated['decision']; // approved or needs_revision

        // Process approval
        $result = $this->fundingService->approveItem(
            $approval,
            $decision,
            $validated['comments'] ?? null,
            $user->id
        );

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
            ], 422);
        }

        // Log the approval
        \App\Models\AuditLog::create([
            'user_id'      => $user->id,
            'action'       => 'funding_item_approved',
            'table_name'   => 'funding_approvals',
            'record_id'    => $approval->id,
            'new_values'   => [
                'decision'  => $decision,
                'comments'  => $validated['comments'] ?? null,
            ],
        ]);

        $approval->refresh()->load('funding', 'approver');

        return response()->json([
            'message' => 'Item approved successfully.',
            'data'    => FundingApprovalResource::make($approval),
        ]);
    }

    /**
     * Reject a funding or expense
     */
    public function reject(FundingApproval $approval, Request $request): JsonResponse
    {
        $this->authorize('approve', $approval);

        $user = $request->user();

        // Verify user is the designated approver
        if ($approval->approver_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized: You are not the designated approver for this item.',
            ], 403);
        }

        // Verify decision is not already made
        if ($approval->decision !== null) {
            return response()->json([
                'message' => 'This approval has already been decided.',
            ], 422);
        }

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        // Process rejection
        $result = $this->fundingService->rejectItem(
            $approval,
            $request->input('reason'),
            $user->id
        );

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
            ], 422);
        }

        // Log the rejection
        \App\Models\AuditLog::create([
            'user_id'      => $user->id,
            'action'       => 'funding_item_rejected',
            'table_name'   => 'funding_approvals',
            'record_id'    => $approval->id,
            'new_values'   => [
                'decision' => 'rejected',
                'reason'   => $request->input('reason'),
            ],
        ]);

        $approval->refresh()->load('funding', 'approver');

        return response()->json([
            'message' => 'Item rejected successfully.',
            'data'    => FundingApprovalResource::make($approval),
        ]);
    }

    /**
     * Get approval history for a funding record
     */
    public function fundingApprovalHistory(Funding $funding): JsonResponse
    {
        $this->authorize('view', $funding);

        $approvals = $funding->approvals()
            ->with('approver')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(
            FundingApprovalResource::collection($approvals)
        );
    }

    /**
     * Get approval history for an expense
     */
    public function expenseApprovalHistory(FundingExpense $expense): JsonResponse
    {
        $this->authorize('view', $expense->funding);

        $approvals = FundingApproval::where('approval_type', 'expense_approval')
            ->where('resource_id', $expense->id)
            ->with('approver')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(
            FundingApprovalResource::collection($approvals)
        );
    }
}
