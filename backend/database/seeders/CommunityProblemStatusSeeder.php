<?php

namespace Database\Seeders;

use App\Models\CommunityProblemStatus;
use Illuminate\Database\Seeder;

class CommunityProblemStatusSeeder extends Seeder
{
    public function run(): void
    {
        CommunityProblemStatus::firstOrCreate(['name' => 'open']);
        CommunityProblemStatus::firstOrCreate(['name' => 'claimed']);
        CommunityProblemStatus::firstOrCreate(['name' => 'completed']);
    }
}
