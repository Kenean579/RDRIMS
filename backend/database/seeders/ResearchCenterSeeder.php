<?php

namespace Database\Seeders;

use App\Models\ResearchCenter;
use Illuminate\Database\Seeder;

class ResearchCenterSeeder extends Seeder
{
    public function run(): void
    {
        ResearchCenter::create([
            'name' => 'ICT and Digital Innovation Research Center',
            'code' => 'ICT-DIRC',
            'parent_university_id' => 1,
            'description' => 'Research in artificial intelligence, cybersecurity, and digital transformation.',
        ]);

        ResearchCenter::create([
            'name' => 'Climate Change and Environmental Research Center',
            'code' => 'CCERC',
            'parent_university_id' => 1,
            'description' => 'Research on climate adaptation, mitigation, and environmental sustainability.',
        ]);

        ResearchCenter::create([
            'name' => 'Public Health and Epidemiology Research Center',
            'code' => 'PHERC',
            'parent_university_id' => 1,
            'description' => 'Community health research, disease surveillance, and health systems strengthening.',
        ]);

        ResearchCenter::create([
            'name' => 'Renewable Energy Research Center',
            'code' => 'RERC',
            'parent_university_id' => 1,
            'description' => 'Solar, wind, and hydro energy research for sustainable development.',
        ]);

        ResearchCenter::create([
            'name' => 'Ethiopian Studies Center',
            'code' => 'AAU-ESC',
            'parent_university_id' => 2,
            'description' => 'Interdisciplinary research on Ethiopian history, culture, and languages.',
        ]);
    }
}