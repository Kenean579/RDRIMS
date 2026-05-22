<?php

namespace Database\Seeders;

use App\Models\PublicationAccessType;
use Illuminate\Database\Seeder;

class PublicationAccessTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach (array (
  0 => 
  array (
    'name' => 'open',
    'label' => 'Open Access',
  ),
  1 => 
  array (
    'name' => 'restricted',
    'label' => 'Restricted Access',
  ),
) as $row) {
            PublicationAccessType::updateOrCreate(['name' => $row['name']], $row);
        }
    }
}
