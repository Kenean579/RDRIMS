<?php

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\University;
use Illuminate\Database\Seeder;

class CampusSeeder extends Seeder
{
    public function run(): void
    {
        $wolloUniversity = University::where('code', 'WU')->first();
        $aauUniversity = University::where('code', 'AAU')->first();
        $bduUniversity = University::where('code', 'BDU')->first();

        /*
        |--------------------------------------------------------------------------
        | Wollo University Campuses
        |--------------------------------------------------------------------------
        */

        Campus::updateOrCreate(
            ['code' => 'WU-DESSIE'],
            [
                'name' => 'Dessie Campus',
                'university_id' => $wolloUniversity?->id,
            ]
        );

        Campus::updateOrCreate(
            ['code' => 'WU-KOMBOLCHA'],
            [
                'name' => 'Kombolcha Campus',
                'university_id' => $wolloUniversity?->id,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Addis Ababa University Campuses
        |--------------------------------------------------------------------------
        */

        Campus::updateOrCreate(
            ['code' => 'AAU-SK'],
            [
                'name' => 'Siddist Kilo Campus',
                'university_id' => $aauUniversity?->id,
            ]
        );

        Campus::updateOrCreate(
            ['code' => 'AAU-AK'],
            [
                'name' => 'Arat Kilo Campus',
                'university_id' => $aauUniversity?->id,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Bahir Dar University Campuses
        |--------------------------------------------------------------------------
        */

        Campus::updateOrCreate(
            ['code' => 'BDU-MAIN'],
            [
                'name' => 'Main Campus',
                'university_id' => $bduUniversity?->id,
            ]
        );

        Campus::updateOrCreate(
            ['code' => 'BDU-POLY'],
            [
                'name' => 'Poly Campus',
                'university_id' => $bduUniversity?->id,
            ]
        );
    }
}
