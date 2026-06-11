<?php

namespace Database\Seeders;

use App\Models\Campus;
use Illuminate\Database\Seeder;

class CampusSeeder extends Seeder
{
    public function run(): void
    {
        Campus::firstOrCreate(['name' => 'Dessie Campus', 'code' => 'WU-DESSIE', 'university_id' => 1]);
        Campus::firstOrCreate(['name' => 'Kombolcha Campus', 'code' => 'WU-KOMBOLCHA', 'university_id' => 1]);
        Campus::firstOrCreate(['name' => 'Siddist Kilo Campus', 'code' => 'AAU-SK', 'university_id' => 2]);
        Campus::firstOrCreate(['name' => 'Arat Kilo Campus', 'code' => 'AAU-AK', 'university_id' => 2]);
        Campus::firstOrCreate(['name' => 'Main Campus', 'code' => 'BDU-MAIN', 'university_id' => 3]);
        Campus::firstOrCreate(['name' => 'Poly Campus', 'code' => 'BDU-POLY', 'university_id' => 3]);
    }
}
