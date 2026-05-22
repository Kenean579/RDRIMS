<?php

namespace Database\Seeders;

use App\Models\Locale;
use Illuminate\Database\Seeder;

class LocaleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (array (
  0 => 
  array (
    'name' => 'en',
    'label' => 'English',
  ),
  1 => 
  array (
    'name' => 'am',
    'label' => 'Amharic',
  ),
) as $row) {
            Locale::updateOrCreate(['name' => $row['name']], $row);
        }
    }
}
