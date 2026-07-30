<?php

namespace Database\Factories;

use App\Models\Output;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Output>
 */
class OutputFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'abstract' => fake()->paragraph(),
            'start_date' => now()->subMonths(6)->toDateString(),
            'end_date' => now()->toDateString(),
            'budget' => fake()->numberBetween(5000, 50000),
            'status_id' => \App\Models\OutputStatus::where('name', 'draft')->first()?->id ?? 1,
        ];
    }
}
