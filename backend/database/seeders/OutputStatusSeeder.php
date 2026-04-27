<?php

namespace Database\Seeders;

use App\Models\OutputStatus;
use Illuminate\Database\Seeder;

class OutputStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = ['draft', 'submitted', 'approved_by_supervisor', 'approved', 'rejected'];
        foreach ($statuses as $status) {
            OutputStatus::updateOrCreate(['name' => $status]);
        }
    }
}
