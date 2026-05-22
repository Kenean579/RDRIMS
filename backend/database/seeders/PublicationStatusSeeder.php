<?php

namespace Database\Seeders;

use App\Models\PublicationStatus;
use Illuminate\Database\Seeder;

class PublicationStatusSeeder extends Seeder
{
    public function run(): void
    {
        foreach (array (
  0 => 
  array (
    'name' => 'in_press',
    'label' => 'In Press',
  ),
  1 => 
  array (
    'name' => 'published',
    'label' => 'Published',
  ),
) as $row) {
            PublicationStatus::updateOrCreate(['name' => $row['name']], $row);
        }
    }
}
