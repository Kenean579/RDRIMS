<?php

namespace Database\Seeders;

use App\Models\MilestoneStatus;
use Illuminate\Database\Seeder;

class MilestoneStatusSeeder extends Seeder
{
    public function run(): void
    {
        MilestoneStatus::create(['name' => 'pending']);
        MilestoneStatus::create(['name' => 'done']);
    }
}