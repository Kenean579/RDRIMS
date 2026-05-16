<?php

namespace Database\Seeders;

use App\Models\StudentLevel;
use Illuminate\Database\Seeder;

class StudentLevelSeeder extends Seeder
{
    public function run(): void
    {
        StudentLevel::create(['name' => 'undergraduate']);
        StudentLevel::create(['name' => 'graduate']);
        StudentLevel::create(['name' => 'phd']);
    }
}