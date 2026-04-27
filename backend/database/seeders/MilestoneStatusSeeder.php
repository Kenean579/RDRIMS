<?php

namespace Database\Seeders;

use App\Models\MilestoneStatus;
use Illuminate\Database\Seeder;

class MilestoneStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = ['pending', 'done'];
        foreach ($statuses as $status) {
            MilestoneStatus::updateOrCreate(['name' => $status]);
        }
    }
}
