<?php

namespace Database\Seeders;

use App\Models\ReviewDecision;
use Illuminate\Database\Seeder;

class ReviewDecisionSeeder extends Seeder
{
    public function run(): void
    {
        ReviewDecision::create(['name' => 'accept']);
        ReviewDecision::create(['name' => 'minor']);
        ReviewDecision::create(['name' => 'major']);
        ReviewDecision::create(['name' => 'reject']);
    }
}