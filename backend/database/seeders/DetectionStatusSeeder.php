<?php

namespace Database\Seeders;

use App\Models\DetectionStatus;
use Illuminate\Database\Seeder;

class DetectionStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = ['pending', 'processing', 'completed', 'failed'];
        foreach ($statuses as $status) {
            DetectionStatus::updateOrCreate(['name' => $status]);
        }
    }
}
