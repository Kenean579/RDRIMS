<?php

namespace Database\Seeders;

use App\Models\ProposalType;
use Illuminate\Database\Seeder;

class ProposalTypeSeeder extends Seeder
{
    public function run(): void
    {
        ProposalType::firstOrCreate(['name' => 'sr']);
        ProposalType::firstOrCreate(['name' => 'sp']);
        ProposalType::firstOrCreate(['name' => 'thesis']);
    }
}
