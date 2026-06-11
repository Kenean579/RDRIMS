<?php

namespace Database\Seeders;

use App\Models\ResearchCenter;
use Illuminate\Database\Seeder;

class ResearchCenterSeeder extends Seeder
{
    public function run(): void
    {
        ResearchCenter::firstOrCreate([
            'name' => 'ICT and Digital Innovation Research Center',
            'code' => 'ICT-DIRC',
            'parent_university_id' => 1,
            'description' => 'Research in AI, cybersecurity, and digital transformation.'
        ]);
        ResearchCenter::firstOrCreate([
            'name' => 'Climate Change and Environmental Research Center',
            'code' => 'CCERC',
            'parent_university_id' => 1,
            'description' => 'Climate adaptation and environmental sustainability research.'
        ]);
        ResearchCenter::firstOrCreate([
            'name' => 'Public Health and Epidemiology Research Center',
            'code' => 'PHERC',
            'parent_university_id' => 1,
            'description' => 'Community health and disease surveillance research.'
        ]);
        ResearchCenter::firstOrCreate([
            'name' => 'Renewable Energy Research Center',
            'code' => 'RERC',
            'parent_campus_id' => 1,
            'description' => 'Solar, wind, and hydro energy research.'
        ]);
        ResearchCenter::firstOrCreate([
            'name' => 'Ethiopian Studies Center',
            'code' => 'AAU-ESC',
            'parent_university_id' => 2,
            'description' => 'Interdisciplinary research on Ethiopian history and culture.'
        ]);
    }
}
