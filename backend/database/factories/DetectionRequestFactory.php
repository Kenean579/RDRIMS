<?php

namespace Database\Factories;

use App\Models\DetectionRequest;
use App\Models\DetectionService;
use App\Models\DetectionStatus;
use App\Models\File;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetectionRequest>
 */
class DetectionRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $pendingStatus = DetectionStatus::where('name', 'pending')->first();
        
        return [
            'detectable_type' => 'Proposal',
            'detectable_id' => 1,
            'file_id' => File::factory(),
            'service_id' => DetectionService::factory(),
            'status_id' => $pendingStatus?->id ?? 1,
            'requested_by' => User::factory(),
            'requested_at' => now(),
        ];
    }

    public function completed(): static
    {
        $completedStatus = DetectionStatus::where('name', 'completed')->first();
        
        return $this->state(fn (array $attributes) => [
            'status_id' => $completedStatus?->id ?? 3,
            'completed_at' => now(),
            'completed_by' => User::factory(),
        ]);
    }

    public function failed(): static
    {
        $failedStatus = DetectionStatus::where('name', 'failed')->first();
        
        return $this->state(fn (array $attributes) => [
            'status_id' => $failedStatus?->id ?? 4,
        ]);
    }

    public function reviewed(): static
    {
        return $this->state(fn (array $attributes) => [
            'reviewed_at' => now(),
            'reviewed_by' => User::factory(),
        ]);
    }
}
