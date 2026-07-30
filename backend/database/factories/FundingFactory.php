<?php

namespace Database\Factories;

use App\Models\FundingSource;
use App\Models\FundingStatus;
use App\Models\Project;
use App\Models\Proposal;
use App\Models\University;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FundingFactory extends Factory
{
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-1 year', 'now');
        $endDate = $this->faker->dateTimeBetween($startDate, '+2 years');

        return [
            'university_id'     => University::factory(),
            'funding_source_id' => FundingSource::factory(),
            'project_id'        => null,
            'proposal_id'       => null,
            'status_id'         => FundingStatus::where('name', 'draft')->first()?->id ?? 1,
            'reference_number'  => $this->faker->unique()->regexify('[A-Z]{3}-\d{4}-\d{3}'),
            'title'             => $this->faker->sentence(6),
            'description'       => $this->faker->paragraph(),
            'total_amount'      => $this->faker->numberBetween(10000, 1000000),
            'currency'          => 'USD',
            'start_date'        => $startDate,
            'end_date'          => $endDate,
            'is_internal'       => $this->faker->boolean(),
            'created_by'        => User::factory(),
            'approved_by'       => null,
            'approved_at'       => null,
        ];
    }

    public function withProject(): self
    {
        return $this->state([
            'project_id' => Project::factory(),
        ]);
    }

    public function withProposal(): self
    {
        return $this->state([
            'proposal_id' => Proposal::factory(),
        ]);
    }

    public function approved(): self
    {
        return $this->state([
            'status_id'   => FundingStatus::where('name', 'approved')->first()?->id ?? 4,
            'approved_by' => User::factory(),
            'approved_at' => now(),
        ]);
    }

    public function submitted(): self
    {
        return $this->state([
            'status_id' => FundingStatus::where('name', 'submitted')->first()?->id ?? 2,
        ]);
    }
}
