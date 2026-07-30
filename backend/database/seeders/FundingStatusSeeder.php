<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FundingStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            'draft',
            'submitted',
            'under_review',
            'approved',
            'rejected',
            'closed',
        ];

        foreach ($statuses as $status) {
            DB::table('funding_statuses')->insertOrIgnore([
                'name' => $status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
