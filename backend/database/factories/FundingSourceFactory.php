<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class FundingSourceFactory extends Factory
{
    public function definition(): array
    {
        $sources = ['Government Grant', 'Private Donation', 'NGO', 'International Organization', 'Corporate Sponsorship'];

        return [
            'name'        => $this->faker->randomElement($sources),
            'description' => $this->faker->sentence(),
            'is_active'   => true,
        ];
    }
}
