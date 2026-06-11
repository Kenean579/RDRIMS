<?php

namespace Database\Seeders;

use App\Models\FinanceCheckStatus;
use Illuminate\Database\Seeder;

class FinanceCheckStatusSeeder extends Seeder
{
    public function run(): void
    {
        FinanceCheckStatus::firstOrCreate(['name' => 'pending']);
        FinanceCheckStatus::firstOrCreate(['name' => 'approved']);
        FinanceCheckStatus::firstOrCreate(['name' => 'rejected']);
    }
}
