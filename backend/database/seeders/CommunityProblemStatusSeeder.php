<?php

namespace Database\Seeders;

use App\Models\CommunityProblemStatus;
use Illuminate\Database\Seeder;

class CommunityProblemStatusSeeder extends Seeder
{
    public function run(): void
    {
        CommunityProblemStatus::create(['name' => 'open']);
        CommunityProblemStatus::create(['name' => 'claimed']);
        CommunityProblemStatus::create(['name' => 'completed']);
    }
}