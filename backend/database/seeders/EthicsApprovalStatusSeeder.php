<?php

namespace Database\Seeders;

use App\Models\EthicsApprovalStatus;
use Illuminate\Database\Seeder;

class EthicsApprovalStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = ['pending', 'approved', 'rejected'];
        foreach ($statuses as $status) {
            EthicsApprovalStatus::updateOrCreate(['name' => $status]);
        }
    }
}
