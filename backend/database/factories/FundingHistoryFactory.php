<?php

namespace Database\Factories;

use App\Models\Funding;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FundingHistoryFactory extends Factory
{
    public function definition(): array
    {
        $action = $this->faker->randomElement(['created', 'allocated', 'spent', 'approved', 'status_changed']);

        return [
            'funding_id'     => Funding::factory(),
            'action'         => $action,
            'old_status'     => null,
            'new_status'     => $this->faker->randomElement(['draft', 'submitted', 'approved']),
            'amount_changed' => $action === 'spent' ? $this->faker->numberBetween(100, 50000) : null,
            'notes'          => $this->faker->sentence(),
            'user_id'        => User::factory(),
            'created_at'     => now(),
        ];
    }
}
