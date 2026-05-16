<?php

namespace Database\Seeders;

use App\Models\University;
use Illuminate\Database\Seeder;

class UniversitySeeder extends Seeder
{
    public function run(): void
    {
        University::create([
            'name' => 'Wollo University',
            'code' => 'WU',
        ]);

        University::create([
            'name' => 'Addis Ababa University',
            'code' => 'AAU',
        ]);

        University::create([
            'name' => 'Bahir Dar University',
            'code' => 'BDU',
        ]);
    }
}