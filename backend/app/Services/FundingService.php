<?php

namespace App\Services;

use App\Models\Funding;
use App\Models\FundingAllocation;
use App\Models\FundingApproval;
use App\Models\FundingExpense;
use App\Models\FundingHistory;
use App\Models\FundingStatus;
use Illuminate\Support\Facades\DB;

class FundingService
{
    /**
     * Create a funding record
     */
    public function create(array $data, int $userId): Funding
    {
        return DB::transaction(function () use ($data, $userId) {
            $data['created_by'] = $userId;
            
            // Get draft status (pre-seeded)
            $draftStatus = FundingStatus::where('name', 'draft')->first();
            if (!$draftStatus) {
                throw new \Exception('Draft status not found. Please seed funding statuses.');
            }
            $data['status_id'] = $draftStatus->id;

            $funding = Funding::create($data);

            // Log creation
            $this->logHistory($funding, 'created', $userId, "Funding {$funding->reference_number} created");

            return $funding->fresh(['status', 'fundingSource', 'createdBy']);
        });
    }

    /**
     * Update a funding record
     */
    public function update(Funding $funding, array $data, int $userId): Funding
    {
        return DB::transaction(function () use ($funding, $data, $userId) {
            $oldData = $funding->only(['total_amount', 'title', 'description']);
            $funding->update($data);

            // Log update
            $this->logHistory(
                $funding,
                'updated',
                $userId,
                "Funding {$funding->reference_number} updated",
                ['old' => $oldData, 'new' => $data]
            );

            return $funding;
        });
    }

    /**
     * Submit funding for review
     */
    public function submit(Funding $funding, int $userId): Funding
    {
        return DB::transaction(function () use ($funding, $userId) {
            $submittedStatus = FundingStatus::where('name', 'submitted')->first();
            if (!$submittedStatus) {
                throw new \Exception('Submitted status not found. Please seed funding statuses.');
            }
            
            // Direct assignment for guarded attribute
            $funding->status_id = $submittedStatus->id;
            $funding->save();

            // Log submission
            $this->recordApproval($funding, 'submitted', $userId, 'Funding submitted for review');
            $this->logHistory($funding, 'submitted', $userId, "Funding {$funding->reference_number} submitted");

            return $funding->fresh(['status', 'fundingSource', 'createdBy']);
        });
    }

    /**
     * Approve a funding
     */
    public function approve(Funding $funding, int $userId, ?string $comments = null): Funding
    {
        return DB::transaction(function () use ($funding, $userId, $comments) {
            $approvedStatus = FundingStatus::where('name', 'approved')->first();
            if (!$approvedStatus) {
                throw new \Exception('Approved status not found. Please seed funding statuses.');
            }
            
            // Direct assignment for guarded attributes
            $funding->status_id = $approvedStatus->id;
            $funding->approved_by = $userId;
            $funding->approved_at = now();
            $funding->save();

            // Record approval
            $this->recordApproval($funding, 'approved', $userId, $comments ?? 'Funding approved');
            $this->logHistory($funding, 'approved', $userId, "Funding {$funding->reference_number} approved");

            return $funding->fresh(['status', 'fundingSource', 'createdBy']);
        });
    }

    /**
     * Reject a funding
     */
    public function reject(Funding $funding, int $userId, ?string $comments = null): Funding
    {
        return DB::transaction(function () use ($funding, $userId, $comments) {
            $rejectedStatus = FundingStatus::where('name', 'rejected')->first();
            if (!$rejectedStatus) {
                throw new \Exception('Rejected status not found. Please seed funding statuses.');
            }
            
            // Direct assignment for guarded attribute
            $funding->status_id = $rejectedStatus->id;
            $funding->save();

            // Record rejection
            $this->recordApproval($funding, 'rejected', $userId, $comments ?? 'Funding rejected');
            $this->logHistory($funding, 'rejected', $userId, "Funding {$funding->reference_number} rejected");

            return $funding->fresh(['status', 'fundingSource', 'createdBy']);
        });
    }

    /**
     * Add budget allocation
     */
    public function allocateBudget(Funding $funding, array $allocations, int $userId): void
    {
        DB::transaction(function () use ($funding, $allocations, $userId) {
            foreach ($allocations as $allocation) {
                FundingAllocation::updateOrCreate(
                    [
                        'funding_id' => $funding->id,
                        'budget_category_id' => $allocation['budget_category_id'],
                    ],
                    [
                        'allocated_amount' => $allocation['amount'],
                        'used_amount' => 0,
                    ]
                );
            }

            $this->logHistory(
                $funding,
                'budget_revised',
                $userId,
                "Budget allocations updated"
            );
        });
    }

    /**
     * Record an expense
     */
    public function recordExpense(Funding $funding, array $data, int $userId): FundingExpense
    {
        return DB::transaction(function () use ($funding, $data, $userId) {
            $data['funding_id'] = $funding->id;
            $data['submitted_by'] = $userId;
            $data['status'] = 'pending';

            $expense = FundingExpense::create($data);

            $this->logHistory(
                $funding,
                'expense_submitted',
                $userId,
                "Expense recorded: {$data['reference_number']}"
            );

            return $expense;
        });
    }

    /**
     * Approve an expense
     */
    public function approveExpense(FundingExpense $expense, int $userId, ?string $notes = null): FundingExpense
    {
        return DB::transaction(function () use ($expense, $userId, $notes) {
            // Update used_amount in allocation
            $allocation = FundingAllocation::where('funding_id', $expense->funding_id)
                ->where('budget_category_id', $expense->budget_category_id)
                ->first();

            if ($allocation) {
                $allocation->update([
                    'used_amount' => $allocation->used_amount + $expense->amount,
                ]);
            }

            // Update expense status
            $expense->update([
                'status' => 'approved',
                'approved_by' => $userId,
                'approved_at' => now(),
                'approval_notes' => $notes,
            ]);

            $this->logHistory(
                $expense->funding,
                'expense_approved',
                $userId,
                "Expense approved: {$expense->reference_number}"
            );

            return $expense;
        });
    }

    /**
     * Reject an expense
     */
    public function rejectExpense(FundingExpense $expense, int $userId, ?string $notes = null): FundingExpense
    {
        return DB::transaction(function () use ($expense, $userId, $notes) {
            $expense->update([
                'status' => 'rejected',
                'approved_by' => $userId,
                'approved_at' => now(),
                'approval_notes' => $notes,
            ]);

            $this->logHistory(
                $expense->funding,
                'expense_rejected',
                $userId,
                "Expense rejected: {$expense->reference_number}"
            );

            return $expense;
        });
    }

    /**
     * Record approval action
     */
    private function recordApproval(Funding $funding, string $action, int $userId, ?string $comments = null): void
    {
        FundingApproval::create([
            'funding_id' => $funding->id,
            'action' => $action,
            'approved_by' => $userId,
            'approved_at' => now(),
            'comments' => $comments,
        ]);
    }

    /**
     * Log history entry
     */
    private function logHistory(Funding $funding, string $action, int $userId, string $description, ?array $changes = null): void
    {
        FundingHistory::create([
            'funding_id' => $funding->id,
            'action' => $action,
            'performed_by' => $userId,
            'description' => $description,
            'changes' => $changes,
        ]);
    }

    /**
     * Calculate budget statistics for a funding
     */
    public function getBudgetStats(Funding $funding): array
    {
        $allocations = $funding->allocations()->with('budgetCategory')->get();

        return [
            'total_allocated' => $allocations->sum('allocated_amount'),
            'total_used' => $allocations->sum('used_amount'),
            'total_remaining' => $allocations->sum(function ($a) {
                return $a->getCurrentBudget() - $a->used_amount;
            }),
            'allocations' => $allocations->map(fn($a) => [
                'category' => $a->budgetCategory->name,
                'allocated' => $a->allocated_amount,
                'used' => $a->used_amount,
                'remaining' => $a->getRemainingBudget(),
                'utilization_percent' => $a->getUtilizationPercentage(),
            ]),
        ];
    }
}
