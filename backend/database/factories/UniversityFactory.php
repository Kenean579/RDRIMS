<?php

namespace Database\Factories;

use App\Models\University;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<University>
 */
class UniversityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->company() . ' University';
        $code = strtoupper($this->faker->unique()->lexify('???'));
        
        return [
            'name' => $name,
            'code' => $code,
            'location' => $this->faker->city() . ', ' . $this->faker->country(),
        ];
    }
}
