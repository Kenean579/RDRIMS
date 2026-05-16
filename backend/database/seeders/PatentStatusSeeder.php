<?php

namespace Database\Seeders;

use App\Models\PatentStatus;
use Illuminate\Database\Seeder;

class PatentStatusSeeder extends Seeder
{
    public function run(): void
    {
        PatentStatus::create(['name' => 'pending']);
        PatentStatus::create(['name' => 'granted']);
        PatentStatus::create(['name' => 'expired']);
    }
}