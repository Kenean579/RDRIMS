<?php

namespace Database\Seeders;

use App\Models\OutputStatus;
use Illuminate\Database\Seeder;

class OutputStatusSeeder extends Seeder
{
    public function run(): void
    {
        OutputStatus::create(['name' => 'draft']);
        OutputStatus::create(['name' => 'submitted']);
        OutputStatus::create(['name' => 'approved_by_supervisor']);
        OutputStatus::create(['name' => 'approved']);
        OutputStatus::create(['name' => 'rejected']);
    }
}