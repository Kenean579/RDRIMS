<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BudgetCategoryFactory extends Factory
{
    public function definition(): array
    {
        $categories = ['Personnel', 'Equipment', 'Supplies', 'Travel', 'Indirect Costs'];

        return [
            'name'        => $this->faker->randomElement($categories),
            'description' => $this->faker->sentence(),
            'is_active'   => true,
        ];
    }
}
