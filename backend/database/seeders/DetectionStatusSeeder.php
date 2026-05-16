<?php

namespace Database\Seeders;

use App\Models\DetectionStatus;
use Illuminate\Database\Seeder;

class DetectionStatusSeeder extends Seeder
{
    public function run(): void
    {
        DetectionStatus::create(['name' => 'pending']);
        DetectionStatus::create(['name' => 'processing']);
        DetectionStatus::create(['name' => 'completed']);
        DetectionStatus::create(['name' => 'failed']);
    }
}