<?php

namespace Database\Seeders;

use App\Models\StudentLevel;
use Illuminate\Database\Seeder;

class StudentLevelSeeder extends Seeder
{
    public function run(): void
    {
        StudentLevel::firstOrCreate(['name' => 'undergraduate']);
        StudentLevel::firstOrCreate(['name' => 'graduate']);
        StudentLevel::firstOrCreate(['name' => 'phd']);
    }
}
