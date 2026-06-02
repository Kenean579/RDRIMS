<?php

namespace Database\Seeders;

use App\Models\ProposalType;
use Illuminate\Database\Seeder;

class ProposalTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            'Small Research',
            'Strategic Project',
            'Institutional Project',
            'semester project',
            'final year project',
            'Postgraduate Thesis',
            'Innovation Grant',
            'Collaborative Research',
            'Industry-Sponsored Research',
        ];

        foreach ($types as $type) {
            ProposalType::create(['name' => $type]);
        }
    }
}