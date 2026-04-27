<?php

namespace Database\Seeders;

use App\Models\FinanceCheckStatus;
use Illuminate\Database\Seeder;

class FinanceCheckStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = ['pending', 'approved', 'rejected'];
        foreach ($statuses as $status) {
            FinanceCheckStatus::updateOrCreate(['name' => $status]);
        }
    }
}
