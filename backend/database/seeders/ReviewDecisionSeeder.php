<?php

namespace Database\Seeders;

use App\Models\ReviewDecision;
use Illuminate\Database\Seeder;

class ReviewDecisionSeeder extends Seeder
{
    public function run(): void
    {
        $decisions = ['accept', 'minor', 'major', 'reject'];
        foreach ($decisions as $decision) {
            ReviewDecision::updateOrCreate(['name' => $decision]);
        }
    }
}
