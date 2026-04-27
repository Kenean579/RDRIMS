<?php

namespace Database\Seeders;

use App\Models\ProposalType;
use Illuminate\Database\Seeder;

class ProposalTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['sr', 'sp', 'thesis'];
        foreach ($types as $type) {
            ProposalType::updateOrCreate(['name' => $type]);
        }
    }
}
