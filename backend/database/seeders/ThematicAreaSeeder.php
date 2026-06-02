<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ThematicAreaSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            ['name' => 'Agriculture & Food Security', 'description' => 'Research on crop science, irrigation, food systems, and agricultural productivity.'],
            ['name' => 'Health & Medicine', 'description' => 'Clinical research, public health, epidemiology, and biomedical sciences.'],
            ['name' => 'Technology & Innovation', 'description' => 'ICT, AI, software engineering, and digital transformation.'],
            ['name' => 'Energy & Environment', 'description' => 'Renewable energy, climate change, water resources, and environmental sustainability.'],
            ['name' => 'Education & Social Sciences', 'description' => 'Pedagogy, curriculum development, economics, and social welfare.'],
            ['name' => 'Engineering & Infrastructure', 'description' => 'Civil, mechanical, electrical engineering and urban infrastructure.'],
            ['name' => 'Natural Resources & Mining', 'description' => 'Geology, mineral resources, and land use management.'],
            ['name' => 'Gender & Development Studies', 'description' => 'Gender equity, community development, and inclusive growth.'],
            ['name' => 'Law & Governance', 'description' => 'Policy research, legal studies, and public administration.'],
            ['name' => 'Arts, Culture & Heritage', 'description' => 'Cultural preservation, linguistics, history, and the arts.'],
        ];

        foreach ($areas as $area) {
            DB::table('thematic_areas')->insertOrIgnore($area + ['is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        }
    }
}
