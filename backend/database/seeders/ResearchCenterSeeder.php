<?php

namespace Database\Seeders;

use App\Models\ResearchCenter;
use App\Models\University;
use App\Models\Campus;
use Illuminate\Database\Seeder;

class ResearchCenterSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Get Organizations Dynamically
        |--------------------------------------------------------------------------
        */

        $wolloUniversity = University::where('code', 'WU')->first();
        $aauUniversity = University::where('code', 'AAU')->first();

        $dessieCampus = Campus::where('code', 'WU-DESSIE')->first();


        /*
        |--------------------------------------------------------------------------
        | Wollo University Research Centers
        |--------------------------------------------------------------------------
        */

        ResearchCenter::updateOrCreate(
            ['code' => 'ICT-DIRC'],
            [
                'name' => 'ICT and Digital Innovation Research Center',
                'parent_university_id' => $wolloUniversity?->id,
                'parent_campus_id' => null,
                'description' => 'Research in artificial intelligence, cybersecurity, software engineering, and digital transformation.',
            ]
        );


        ResearchCenter::updateOrCreate(
            ['code' => 'CCERC'],
            [
                'name' => 'Climate Change and Environmental Research Center',
                'parent_university_id' => $wolloUniversity?->id,
                'parent_campus_id' => null,
                'description' => 'Research on climate adaptation, environmental protection, and sustainability.',
            ]
        );


        ResearchCenter::updateOrCreate(
            ['code' => 'PHERC'],
            [
                'name' => 'Public Health and Epidemiology Research Center',
                'parent_university_id' => $wolloUniversity?->id,
                'parent_campus_id' => null,
                'description' => 'Research on community health, epidemiology, and disease surveillance.',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Wollo Campus-Level Research Center
        |--------------------------------------------------------------------------
        */

        ResearchCenter::updateOrCreate(
            ['code' => 'RERC'],
            [
                'name' => 'Renewable Energy Research Center',
                'parent_university_id' => null,
                'parent_campus_id' => $dessieCampus?->id,
                'description' => 'Research on solar, wind, hydro, and sustainable energy technologies.',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Addis Ababa University Research Centers
        |--------------------------------------------------------------------------
        */

        ResearchCenter::updateOrCreate(
            ['code' => 'AAU-ESC'],
            [
                'name' => 'Ethiopian Studies Center',
                'parent_university_id' => $aauUniversity?->id,
                'parent_campus_id' => null,
                'description' => 'Interdisciplinary research on Ethiopian history, culture, languages, and heritage.',
            ]
        );
    }
}
