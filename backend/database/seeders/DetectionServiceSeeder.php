<?php

namespace Database\Seeders;

use App\Models\DetectionService;
use Illuminate\Database\Seeder;

class DetectionServiceSeeder extends Seeder
{
    public function run(): void
    {
        DetectionService::create(['name' => 'turnitin']);
        DetectionService::create(['name' => 'copyleaks']);
        DetectionService::create(['name' => 'gptzero']);
        DetectionService::create(['name' => 'local_similarity']);
    }
}