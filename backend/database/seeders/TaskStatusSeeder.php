<?php

namespace Database\Seeders;

use App\Models\TaskStatus;
use Illuminate\Database\Seeder;

class TaskStatusSeeder extends Seeder
{
    public function run(): void
    {
        TaskStatus::create(['name' => 'not_started']);
        TaskStatus::create(['name' => 'in_progress']);
        TaskStatus::create(['name' => 'done']);
    }
}