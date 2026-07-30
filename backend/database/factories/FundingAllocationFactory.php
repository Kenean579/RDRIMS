<?php

namespace Database\Factories;

use App\Models\BudgetCategory;
use App\Models\Funding;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FundingAllocationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'funding_id'         => Funding::factory(),
            'budget_category_id' => BudgetCategory::factory(),
            'allocated_amount'   => $this->faker->numberBetween(1000, 100000),
            'notes'              => $this->faker->sentence(),
            'allocated_by'       => User::factory(),
        ];
    }
}
