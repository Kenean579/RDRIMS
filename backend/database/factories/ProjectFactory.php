<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'total_budget' => fake()->numberBetween(10000, 500000),
            'budget_allocation' => null,
            'status_id' => ProjectStatus::where('name', 'draft')->first()?->id ?? ProjectStatus::first()?->id,
            'pi_id' => User::factory(),
            'academic_year_id' => AcademicYear::firstOrCreate(
                ['name' => '2026-2027'],
                ['is_current' => true]
            )->id,
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }

    /**
     * Indicate that the project is in draft status.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_id' => ProjectStatus::where('name', 'draft')->first()?->id,
        ]);
    }

    /**
     * Indicate that the project is in planning status.
     */
    public function planning(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_id' => ProjectStatus::where('name', 'planning')->first()?->id,
        ]);
    }

    /**
     * Indicate that the project is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_id' => ProjectStatus::where('name', 'active')->first()?->id,
        ]);
    }

    /**
     * Indicate that the project is suspended.
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_id' => ProjectStatus::where('name', 'suspended')->first()?->id,
        ]);
    }

    /**
     * Indicate that the project is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_id' => ProjectStatus::where('name', 'completed')->first()?->id,
        ]);
    }
}
