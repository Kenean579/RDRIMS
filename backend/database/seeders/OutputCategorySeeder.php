<?php

namespace Database\Seeders;

use App\Models\OutputCategory;
use Illuminate\Database\Seeder;

class OutputCategorySeeder extends Seeder
{
    public function run(): void
    {
        OutputCategory::firstOrCreate(['name' => 'research_center']);
        OutputCategory::firstOrCreate(['name' => 'student']);
    }
}
