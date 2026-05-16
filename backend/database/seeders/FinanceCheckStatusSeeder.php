<?php

namespace Database\Seeders;

use App\Models\FinanceCheckStatus;
use Illuminate\Database\Seeder;

class FinanceCheckStatusSeeder extends Seeder
{
    public function run(): void
    {
        FinanceCheckStatus::create(['name' => 'pending']);
        FinanceCheckStatus::create(['name' => 'approved']);
        FinanceCheckStatus::create(['name' => 'rejected']);
    }
}