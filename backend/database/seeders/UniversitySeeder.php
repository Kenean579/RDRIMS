<?php

namespace Database\Seeders;

use App\Models\University;
use Illuminate\Database\Seeder;

class UniversitySeeder extends Seeder
{
    public function run(): void
    {
        University::firstOrCreate(['name' => 'Wollo University', 'code' => 'WU', 'location' => 'Dessie, Ethiopia']);
        University::firstOrCreate(['name' => 'Addis Ababa University', 'code' => 'AAU', 'location' => 'Addis Ababa, Ethiopia']);
        University::firstOrCreate(['name' => 'Bahir Dar University', 'code' => 'BDU', 'location' => 'Bahir Dar, Ethiopia']);
        University::firstOrCreate(['name' => 'Gonder University', 'code' => 'GoU', 'location' => 'Gonder, Ethiopia']);
    }
}
