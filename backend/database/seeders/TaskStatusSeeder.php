<?php

namespace Database\Seeders;

use App\Models\TaskStatus;
use Illuminate\Database\Seeder;

class TaskStatusSeeder extends Seeder
{
    public function run(): void
    {
        TaskStatus::firstOrCreate(['name' => 'not_started']);
        TaskStatus::firstOrCreate(['name' => 'in_progress']);
        TaskStatus::firstOrCreate(['name' => 'done']);
    }
}
