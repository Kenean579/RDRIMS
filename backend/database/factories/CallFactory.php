<?php

namespace Database\Factories;

use App\Models\Call;
use App\Models\CallStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Call>
 */
class CallFactory extends Factory
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
            'description' => fake()->paragraphs(3, true),
            'deadline' => now()->addDays(30),
            'thematic_areas' => fake()->words(5, true),
            'created_by' => User::factory(),
            'status_id' => CallStatus::firstOrCreate(['name' => 'open'], ['description' => 'Open'])->id,
            'published_at' => now(),
            'opens_at' => now(),
            'closes_at' => now()->addDays(30),
            'is_featured' => false,
            'is_public' => true,
            
            // Required field - set to 1 by default for tests
            'university_id' => 1,
            
            // Optional hierarchical fields
            'academic_year_id' => null,
            'guideline_file_id' => null,
            'research_center_id' => null,
            'campus_id' => null,
            'faculty_id' => null,
            'department_id' => null,
            'metadata' => null,
        ];
    }

    /**
     * Indicate that the call has a specific deadline.
     */
    public function withDeadline(\DateTimeInterface $deadline): static
    {
        return $this->state(fn (array $attributes) => [
            'deadline' => $deadline,
            'closes_at' => $deadline,
        ]);
    }

    /**
     * Indicate that the call is closed.
     */
    public function closed(): static
    {
        $closedStatus = CallStatus::firstOrCreate(['name' => 'closed'], ['description' => 'Closed']);
        
        return $this->state(fn (array $attributes) => [
            'status_id' => $closedStatus->id,
            'deadline' => now()->subDays(1),
            'closes_at' => now()->subDays(1),
        ]);
    }

    /**
     * Indicate that the call belongs to a university.
     */
    public function forUniversity(int $universityId): static
    {
        return $this->state(fn (array $attributes) => [
            'university_id' => $universityId,
        ]);
    }
}
