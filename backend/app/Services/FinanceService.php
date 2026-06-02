<?php

namespace App\Services;

use App\Models\FinanceCheck;
use App\Models\Proposal;
use App\Models\User;
use App\Models\FinanceCheckStatus;

class FinanceService
{
    public function createCheck(Proposal $proposal, User $requestedBy): FinanceCheck
    {
        return $proposal->financeChecks()->create([
            'status_id' => FinanceCheckStatus::where('name', 'pending')->first()->id ?? 1,
            'checked_by' => null, // Not yet checked
            'comments' => null,
        ]);
    }

    public function approve(FinanceCheck $check, User $officer, ?string $comment = null): void
    {
        $statusId = FinanceCheckStatus::where('name', 'approved')->first()->id 
            ?? FinanceCheckStatus::where('name', 'cleared')->first()->id;

        $check->update([
            'status_id' => $statusId,
            'checked_by' => $officer->id,
            'check_date' => now(),
            'comments' => $comment,
        ]);
    }

    public function reject(FinanceCheck $check, User $officer, string $comment): void
    {
        $check->update([
            'status_id' => FinanceCheckStatus::where('name', 'rejected')->first()->id,
            'checked_by' => $officer->id,
            'check_date' => now(),
            'comments' => $comment,
        ]);
    }
}
