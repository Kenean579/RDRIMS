<?php

namespace Database\Seeders;

use App\Models\ProjectStatus;
use Illuminate\Database\Seeder;

class ProjectStatusSeeder extends Seeder
{
    public function run(): void
    {
        ProjectStatus::create(['name' => 'active']);
        ProjectStatus::create(['name' => 'completed']);
        ProjectStatus::create(['name' => 'suspended']);
    }
}