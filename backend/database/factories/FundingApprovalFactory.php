<?php

namespace Database\Factories;

use App\Models\Funding;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FundingApprovalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'funding_id'     => Funding::factory(),
            'approval_type'  => 'fund_approval',
            'resource_id'    => null,
            'current_status' => 'submitted',
            'approver_id'    => User::factory(),
            'decision'       => null,
            'comments'       => null,
            'decided_at'     => null,
        ];
    }

    public function approved(): self
    {
        return $this->state([
            'decision'   => 'approved',
            'comments'   => 'Approved for funding.',
            'decided_at' => now(),
        ]);
    }

    public function rejected(): self
    {
        return $this->state([
            'decision'   => 'rejected',
            'comments'   => 'Does not meet funding criteria.',
            'decided_at' => now(),
        ]);
    }

    public function forExpense(): self
    {
        return $this->state([
            'approval_type' => 'expense_approval',
        ]);
    }
}
