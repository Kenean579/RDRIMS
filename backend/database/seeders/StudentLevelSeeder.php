<?php

namespace Database\Seeders;

use App\Models\StudentLevel;
use Illuminate\Database\Seeder;

class StudentLevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = ['undergraduate', 'graduate', 'phd'];
        foreach ($levels as $level) {
            StudentLevel::updateOrCreate(['name' => $level]);
        }
    }
}
