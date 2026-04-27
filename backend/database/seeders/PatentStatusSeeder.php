<?php

namespace Database\Seeders;

use App\Models\PatentStatus;
use Illuminate\Database\Seeder;

class PatentStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = ['pending', 'granted', 'expired'];
        foreach ($statuses as $status) {
            PatentStatus::updateOrCreate(['name' => $status]);
        }
    }
}
