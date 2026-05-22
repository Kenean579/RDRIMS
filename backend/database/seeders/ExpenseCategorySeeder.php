<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (array (
  0 => 
  array (
    'name' => 'travel',
    'label' => 'Travel',
  ),
  1 => 
  array (
    'name' => 'equipment',
    'label' => 'Equipment',
  ),
  2 => 
  array (
    'name' => 'supplies',
    'label' => 'Supplies',
  ),
  3 => 
  array (
    'name' => 'personnel',
    'label' => 'Personnel',
  ),
  4 => 
  array (
    'name' => 'others',
    'label' => 'Others',
  ),
) as $row) {
            ExpenseCategory::updateOrCreate(['name' => $row['name']], $row);
        }
    }
}
