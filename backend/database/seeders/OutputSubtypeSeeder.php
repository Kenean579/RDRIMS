<?php

namespace Database\Seeders;

use App\Models\OutputSubtype;
use Illuminate\Database\Seeder;

class OutputSubtypeSeeder extends Seeder
{
    public function run(): void
    {
        $subtypes = ['internship', 'final_year_project', 'semester_project', 'thesis', 'research_paper', 'dataset', 'report', 'patent'];
        foreach ($subtypes as $subtype) {
            OutputSubtype::updateOrCreate(['name' => $subtype]);
        }
    }
}
