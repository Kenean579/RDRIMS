<?php

namespace Database\Factories;

use App\Models\Publication;
use App\Models\PublicationStatus;
use App\Models\PublicationType;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Publication>
 */
class PublicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'type_id' => PublicationType::where('name', 'journal_article')->first()?->id ?? PublicationType::first()?->id,
            'status_id' => PublicationStatus::where('name', 'draft')->first()?->id ?? PublicationStatus::first()?->id,
            'title' => fake()->sentence(),
            'abstract' => fake()->paragraph(),
            'keywords' => fake()->words(5, true),
            'journal' => fake()->company() . ' Journal',
            'volume' => fake()->numberBetween(1, 50),
            'issue' => fake()->numberBetween(1, 12),
            'pages' => fake()->numberBetween(1, 50) . '-' . fake()->numberBetween(51, 100),
            'doi' => '10.' . fake()->numberBetween(1000, 9999) . '/' . fake()->lexify('????.????'),
            'publication_date' => fake()->date(),
            'citation_count' => fake()->numberBetween(0, 100),
        ];
    }

    /**
     * Indicate that the publication is in draft status.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_id' => PublicationStatus::where('name', 'draft')->first()?->id,
        ]);
    }

    /**
     * Indicate that the publication is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_id' => PublicationStatus::where('name', 'published')->first()?->id,
        ]);
    }
}
