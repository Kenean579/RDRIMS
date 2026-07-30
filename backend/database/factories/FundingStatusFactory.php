<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class FundingStatusFactory extends Factory
{
    public function definition(): array
    {
        $statuses = ['draft', 'submitted', 'under_review', 'approved', 'rejected', 'completed'];

        return [
            'name'        => $this->faker->randomElement($statuses),
            'description' => $this->faker->sentence(),
        ];
    }
}
