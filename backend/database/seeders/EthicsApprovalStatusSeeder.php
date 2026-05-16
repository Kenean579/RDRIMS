<?php

namespace Database\Seeders;

use App\Models\EthicsApprovalStatus;
use Illuminate\Database\Seeder;

class EthicsApprovalStatusSeeder extends Seeder
{
    public function run(): void
    {
        EthicsApprovalStatus::create(['name' => 'pending']);
        EthicsApprovalStatus::create(['name' => 'approved']);
        EthicsApprovalStatus::create(['name' => 'rejected']);
    }
}