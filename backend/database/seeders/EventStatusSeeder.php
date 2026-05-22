<?php

namespace Database\Seeders;

use App\Models\EventStatus;
use Illuminate\Database\Seeder;

class EventStatusSeeder extends Seeder
{
    public function run(): void
    {
        foreach (array (
  0 => 
  array (
    'name' => 'draft',
    'label' => 'Draft',
  ),
  1 => 
  array (
    'name' => 'open',
    'label' => 'Open',
  ),
  2 => 
  array (
    'name' => 'closed',
    'label' => 'Closed',
  ),
  3 => 
  array (
    'name' => 'completed',
    'label' => 'Completed',
  ),
) as $row) {
            EventStatus::updateOrCreate(['name' => $row['name']], $row);
        }
    }
}
