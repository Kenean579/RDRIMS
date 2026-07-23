<?php

namespace Database\Factories;

use App\Models\Proposal;
use App\Models\ProposalStatus;
use App\Models\ProposalType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Proposal>
 */
class ProposalFactory extends Factory
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
            'abstract' => fake()->paragraphs(3, true),
            'objectives' => fake()->paragraphs(2, true),
            'methodology' => fake()->paragraphs(2, true),
            'keywords' => fake()->words(5, true),
            'budget' => fake()->randomFloat(2, 1000, 100000),
            'budget_allocation' => null,
            'status_change_comment' => null,
            'type_id' => ProposalType::firstOrCreate(['name' => 'research'], ['description' => 'Research'])->id,
            'status_id' => ProposalStatus::firstOrCreate(['name' => 'draft'], ['description' => 'Draft'])->id,
            'submitted_by' => User::factory(),
            'submitted_at' => now(),
            
            // Optional fields
            'call_id' => null,
            'approved_by' => null,
            'approved_at' => null,
            'academic_year_id' => null,
            'file_id' => null,
            'ethics_file_id' => null,
            'ethics_approval_status_id' => null,
            'research_center_id' => null,
        ];
    }

    /**
     * Indicate that the proposal is a draft.
     */
    public function draft(): static
    {
        $draftStatus = ProposalStatus::firstOrCreate(['name' => 'draft'], ['description' => 'Draft']);
        
        return $this->state(fn (array $attributes) => [
            'status_id' => $draftStatus->id,
            'submitted_at' => null,
        ]);
    }

    /**
     * Indicate that the proposal is submitted.
     */
    public function submitted(): static
    {
        $submittedStatus = ProposalStatus::firstOrCreate(['name' => 'submitted'], ['description' => 'Submitted']);
        
        return $this->state(fn (array $attributes) => [
            'status_id' => $submittedStatus->id,
            'submitted_at' => now(),
        ]);
    }

    /**
     * Indicate that the proposal belongs to a specific call.
     */
    public function forCall(int $callId): static
    {
        return $this->state(fn (array $attributes) => [
            'call_id' => $callId,
        ]);
    }

    /**
     * Indicate that the proposal is submitted by a specific user.
     */
    public function submittedBy(int $userId): static
    {
        return $this->state(fn (array $attributes) => [
            'submitted_by' => $userId,
        ]);
    }
}
