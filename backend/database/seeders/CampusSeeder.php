<?php

namespace Database\Seeders;

use App\Models\Campus;
use Illuminate\Database\Seeder;

class CampusSeeder extends Seeder
{
    public function run(): void
    {
        // Wollo University (id=1)
        Campus::create(['name' => 'Dessie Campus', 'code' => 'WU-DESSIE', 'university_id' => 1]);
        Campus::create(['name' => 'Kombolcha Campus', 'code' => 'WU-KOMBOLCHA', 'university_id' => 1]);

        // Addis Ababa University (id=2)
        Campus::create(['name' => 'Siddist Kilo Campus', 'code' => 'AAU-SK', 'university_id' => 2]);
        Campus::create(['name' => 'Arat Kilo Campus', 'code' => 'AAU-AK', 'university_id' => 2]);
        Campus::create(['name' => 'Amist Kilo Campus', 'code' => 'AAU-AMK', 'university_id' => 2]);

        // Bahir Dar University (id=3)
        Campus::create(['name' => 'Main Campus', 'code' => 'BDU-MAIN', 'university_id' => 3]);
        Campus::create(['name' => 'Poly Campus', 'code' => 'BDU-POLY', 'university_id' => 3]);
    }
}