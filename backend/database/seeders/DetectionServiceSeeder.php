<?php

namespace Database\Seeders;

use App\Models\DetectionService;
use Illuminate\Database\Seeder;

class DetectionServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = ['turnitin', 'copyleaks', 'gptzero', 'local_similarity', 'plagiarismcheck'];
        foreach ($services as $service) {
            DetectionService::firstOrCreate(['name' => $service]);
        }
    }
}
