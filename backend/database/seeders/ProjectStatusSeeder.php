<?php

namespace Database\Seeders;

use App\Models\ProjectStatus;
use Illuminate\Database\Seeder;

class ProjectStatusSeeder extends Seeder
{
    public function run(): void
    {
        ProjectStatus::firstOrCreate(['name' => 'active']);
        ProjectStatus::firstOrCreate(['name' => 'completed']);
        ProjectStatus::firstOrCreate(['name' => 'suspended']);
    }
}
