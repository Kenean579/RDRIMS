<?php

namespace App\Services;

use App\Models\FinanceCheck;
use App\Models\Proposal;
use App\Models\User;

class FinanceService
{
    public function submitCheck(Proposal $proposal, User $user): FinanceCheck
    {
        return $proposal->financeCheck()->create([
            'status' => 'pending',
            'checked_by' => $user->id,
            'check_date' => now(),
        ]);
    }

    public function approve(FinanceCheck $check, User $officer, ?string $comment = null): void
    {
        $check->update([
            'status' => 'cleared',
            'checked_by' => $officer->id,
            'check_date' => now(),
            'comments' => $comment,
        ]);
    }
}
