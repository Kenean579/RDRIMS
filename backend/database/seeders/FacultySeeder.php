<?php

namespace Database\Seeders;

use App\Models\Faculty;
use App\Models\Campus;
use Illuminate\Database\Seeder;

class FacultySeeder extends Seeder
{
    public function run(): void
    {
        // Get campuses dynamically by code
        $dessieCampus = Campus::where('code', 'WU-DESSIE')->first();
        $kombolchaCampus = Campus::where('code', 'WU-KOMBOLCHA')->first();
        $siddistKiloCampus = Campus::where('code', 'AAU-SK')->first();
        $aratKiloCampus = Campus::where('code', 'AAU-AK')->first();

        /*
        |--------------------------------------------------------------------------
        | Wollo University - Dessie Campus
        |--------------------------------------------------------------------------
        */

        Faculty::updateOrCreate(
            ['code' => 'WU-DESSIE-FNCS'],
            [
                'name' => 'Faculty of Natural and Computational Sciences',
                'campus_id' => $dessieCampus?->id,
            ]
        );

        Faculty::updateOrCreate(
            ['code' => 'WU-DESSIE-FSSH'],
            [
                'name' => 'Faculty of Social Sciences and Humanities',
                'campus_id' => $dessieCampus?->id,
            ]
        );

        Faculty::updateOrCreate(
            ['code' => 'WU-DESSIE-FET'],
            [
                'name' => 'Faculty of Engineering and Technology',
                'campus_id' => $dessieCampus?->id,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Wollo University - Kombolcha Campus
        |--------------------------------------------------------------------------
        */

        Faculty::updateOrCreate(
            ['code' => 'WU-KOM-FBE'],
            [
                'name' => 'Faculty of Business and Economics',
                'campus_id' => $kombolchaCampus?->id,
            ]
        );

        Faculty::updateOrCreate(
            ['code' => 'WU-KOM-FHS'],
            [
                'name' => 'Faculty of Health Sciences',
                'campus_id' => $kombolchaCampus?->id,
            ]
        );

        Faculty::updateOrCreate(
            ['code' => 'WU-KOM-FAG'],
            [
                'name' => 'Faculty of Agriculture',
                'campus_id' => $kombolchaCampus?->id,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Addis Ababa University - Siddist Kilo Campus
        |--------------------------------------------------------------------------
        */

        Faculty::updateOrCreate(
            ['code' => 'AAU-SK-CNS'],
            [
                'name' => 'College of Natural Sciences',
                'campus_id' => $siddistKiloCampus?->id,
            ]
        );

        Faculty::updateOrCreate(
            ['code' => 'AAU-SK-CSS'],
            [
                'name' => 'College of Social Sciences',
                'campus_id' => $siddistKiloCampus?->id,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Addis Ababa University - Arat Kilo Campus
        |--------------------------------------------------------------------------
        */

        Faculty::updateOrCreate(
            ['code' => 'AAU-AK-CLG'],
            [
                'name' => 'College of Law and Governance',
                'campus_id' => $aratKiloCampus?->id,
            ]
        );

        Faculty::updateOrCreate(
            ['code' => 'AAU-AK-CE'],
            [
                'name' => 'College of Education',
                'campus_id' => $aratKiloCampus?->id,
            ]
        );
    }
}
