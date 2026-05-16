<?php

namespace Database\Seeders;

use App\Models\ProposalStatus;
use Illuminate\Database\Seeder;

class ProposalStatusSeeder extends Seeder
{
    public function run(): void
    {
        ProposalStatus::create(['name' => 'draft']);
        ProposalStatus::create(['name' => 'submitted']);
        ProposalStatus::create(['name' => 'under_review']);
        ProposalStatus::create(['name' => 'finance_check']);
        ProposalStatus::create(['name' => 'approved']);
        ProposalStatus::create(['name' => 'rejected']);
    }
}