<?php

namespace Database\Seeders;

use App\Models\ProposalType;
use Illuminate\Database\Seeder;

class ProposalTypeSeeder extends Seeder
{
    public function run(): void
    {
        ProposalType::create(['name' => 'sr']);   // Small Research
        ProposalType::create(['name' => 'sp']);   // Strategic Project
        ProposalType::create(['name' => 'thesis']);
    }
}