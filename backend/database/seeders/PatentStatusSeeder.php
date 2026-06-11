<?php

namespace Database\Seeders;

use App\Models\PatentStatus;
use Illuminate\Database\Seeder;

class PatentStatusSeeder extends Seeder
{
    public function run(): void
    {
        PatentStatus::firstOrCreate(['name' => 'pending']);
        PatentStatus::firstOrCreate(['name' => 'granted']);
        PatentStatus::firstOrCreate(['name' => 'expired']);
    }
}
