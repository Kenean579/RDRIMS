<?php

namespace Database\Seeders;

use App\Models\ExpenseStatus;
use Illuminate\Database\Seeder;

class ExpenseStatusSeeder extends Seeder
{
    public function run(): void
    {
        foreach (array (
  0 => 
  array (
    'name' => 'pending',
    'label' => 'Pending',
  ),
  1 => 
  array (
    'name' => 'approved',
    'label' => 'Approved',
  ),
  2 => 
  array (
    'name' => 'rejected',
    'label' => 'Rejected',
  ),
) as $row) {
            ExpenseStatus::updateOrCreate(['name' => $row['name']], $row);
        }
    }
}
