<?php

namespace Database\Seeders;

use App\Models\ProposalStatus;
use Illuminate\Database\Seeder;

class ProposalStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = ['draft', 'submitted', 'under_review', 'finance_check', 'approved', 'rejected'];
        foreach ($statuses as $status) {
            ProposalStatus::updateOrCreate(['name' => $status]);
        }
    }
}
