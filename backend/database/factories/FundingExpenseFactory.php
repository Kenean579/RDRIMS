<?php

namespace Database\Factories;

use App\Models\BudgetCategory;
use App\Models\ExpenseCategory;
use App\Models\Funding;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FundingExpenseFactory extends Factory
{
    public function definition(): array
    {
        $status = $this->faker->randomElement(['pending', 'approved', 'rejected']);
        $now = now();

        return [
            'funding_id'            => Funding::factory(),
            'budget_category_id'    => BudgetCategory::factory(),
            'expense_category_id'   => ExpenseCategory::factory(),
            'reference_number'      => $this->faker->unique()->regexify('EXP-\d{6}'),
            'description'           => $this->faker->sentence(),
            'amount'                => $this->faker->numberBetween(100, 50000),
            'currency'              => 'USD',
            'expense_date'          => $this->faker->dateTimeThisYear(),
            'status'                => $status,
            'submitted_by'          => User::factory(),
            'approved_by'           => $status === 'approved' ? User::factory() : null,
            'approved_at'           => $status === 'approved' ? $now : null,
            'approval_notes'        => $status === 'approved' ? $this->faker->sentence() : null,
        ];
    }

    public function pending(): self
    {
        return $this->state([
            'status'           => 'pending',
            'approved_by'      => null,
            'approved_at'      => null,
            'approval_notes'   => null,
        ]);
    }

    public function approved(): self
    {
        return $this->state([
            'status'           => 'approved',
            'approved_by'      => User::factory(),
            'approved_at'      => now(),
            'approval_notes'   => $this->faker->sentence(),
        ]);
    }

    public function rejected(): self
    {
        return $this->state([
            'status'           => 'rejected',
            'approved_by'      => null,
            'approved_at'      => null,
            'approval_notes'   => $this->faker->sentence(),
        ]);
    }
}
