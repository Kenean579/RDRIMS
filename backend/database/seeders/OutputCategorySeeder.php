<?php

namespace Database\Seeders;

use App\Models\OutputCategory;
use Illuminate\Database\Seeder;

class OutputCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = ['research_center', 'student'];
        foreach ($categories as $category) {
            OutputCategory::updateOrCreate(['name' => $category]);
        }
    }
}
