<?php

namespace Database\Seeders;

use App\Models\ProposalStatus;
use Illuminate\Database\Seeder;

class ProposalStatusSeeder extends Seeder
{
    public function run(): void
    {
        ProposalStatus::firstOrCreate(['name' => 'draft']);
        ProposalStatus::firstOrCreate(['name' => 'submitted']);
        ProposalStatus::firstOrCreate(['name' => 'under_review']);
        ProposalStatus::firstOrCreate(['name' => 'finance_check']);
        ProposalStatus::firstOrCreate(['name' => 'approved']);
        ProposalStatus::firstOrCreate(['name' => 'rejected']);
    }
}
