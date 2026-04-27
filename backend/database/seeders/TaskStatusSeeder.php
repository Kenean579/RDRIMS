<?php

namespace Database\Seeders;

use App\Models\TaskStatus;
use Illuminate\Database\Seeder;

class TaskStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = ['not_started', 'in_progress', 'done'];
        foreach ($statuses as $status) {
            TaskStatus::updateOrCreate(['name' => $status]);
        }
    }
}
