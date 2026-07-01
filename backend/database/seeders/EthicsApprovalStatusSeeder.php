<?php

namespace Database\Seeders;

use App\Models\EthicsApprovalStatus;
use Illuminate\Database\Seeder;

class EthicsApprovalStatusSeeder extends Seeder
{
    public function run(): void
    {
        EthicsApprovalStatus::firstOrCreate(['name' => 'pending']);
        EthicsApprovalStatus::firstOrCreate(['name' => 'under_review']);
        EthicsApprovalStatus::firstOrCreate(['name' => 'approved']);
        EthicsApprovalStatus::firstOrCreate(['name' => 'needs_revision']);
        EthicsApprovalStatus::firstOrCreate(['name' => 'rejected']);
    }
}
