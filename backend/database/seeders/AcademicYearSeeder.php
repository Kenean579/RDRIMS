<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        AcademicYear::firstOrCreate(['name' => '2024/2025', 'start_date' => '2024-09-01', 'end_date' => '2025-08-31', 'is_current' => false]);
        AcademicYear::firstOrCreate(['name' => '2025/2026', 'start_date' => '2025-09-01', 'end_date' => '2026-08-31', 'is_current' => true]);
        AcademicYear::firstOrCreate(['name' => '2026/2027', 'start_date' => '2026-09-01', 'end_date' => '2027-08-31', 'is_current' => false]);
    }
}
