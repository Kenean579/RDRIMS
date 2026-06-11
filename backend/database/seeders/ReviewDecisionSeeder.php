<?php

namespace Database\Seeders;

use App\Models\ReviewDecision;
use Illuminate\Database\Seeder;

class ReviewDecisionSeeder extends Seeder
{
    public function run(): void
    {
        ReviewDecision::firstOrCreate(['name' => 'accept']);
        ReviewDecision::firstOrCreate(['name' => 'minor']);
        ReviewDecision::firstOrCreate(['name' => 'major']);
        ReviewDecision::firstOrCreate(['name' => 'reject']);
    }
}
