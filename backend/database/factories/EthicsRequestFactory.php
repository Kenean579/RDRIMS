<?php

namespace Database\Factories;

use App\Models\EthicsRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EthicsRequest>
 */
class EthicsRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'proposal_id' => \App\Models\Proposal::factory(),
            'generated_pdf_path' => 'ethics/' . $this->faker->sha256() . '.pdf',
            'submitted_to_irb' => false,
            'approval_status_id' => \App\Models\EthicsApprovalStatus::firstOrCreate(['name' => 'pending'])->id,
            'comments' => $this->faker->optional()->paragraph(),
            'version' => 1,
        ];
    }
}
