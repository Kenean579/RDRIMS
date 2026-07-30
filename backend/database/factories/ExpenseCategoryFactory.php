<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseCategoryFactory extends Factory
{
    public function definition(): array
    {
        $categories = ['Equipment Purchase', 'Travel Expense', 'Supplies', 'Service Contract', 'Salary'];

        return [
            'name'        => $this->faker->randomElement($categories),
            'description' => $this->faker->sentence(),
            'is_active'   => true,
        ];
    }
}
