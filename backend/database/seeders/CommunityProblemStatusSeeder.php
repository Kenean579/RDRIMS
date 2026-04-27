<?php

namespace Database\Seeders;

use App\Models\CommunityProblemStatus;
use Illuminate\Database\Seeder;

class CommunityProblemStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = ['open', 'claimed', 'completed'];
        foreach ($statuses as $status) {
            CommunityProblemStatus::updateOrCreate(['name' => $status]);
        }
    }
}
