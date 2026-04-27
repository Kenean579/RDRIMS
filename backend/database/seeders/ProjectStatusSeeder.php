<?php

namespace Database\Seeders;

use App\Models\ProjectStatus;
use Illuminate\Database\Seeder;

class ProjectStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = ['active', 'completed', 'suspended'];
        foreach ($statuses as $status) {
            ProjectStatus::updateOrCreate(['name' => $status]);
        }
    }
}
