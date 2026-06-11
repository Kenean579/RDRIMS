<?php

namespace Database\Seeders;

use App\Models\EthicsApprovalStatus;
use Illuminate\Database\Seeder;

class EthicsApprovalStatusSeeder extends Seeder
{
    public function run(): void
    {
        EthicsApprovalStatus::firstOrCreate(['name' => 'pending']);
        EthicsApprovalStatus::firstOrCreate(['name' => 'approved']);
        EthicsApprovalStatus::firstOrCreate(['name' => 'rejected']);
    }
}
