<?php

namespace Database\Seeders;

use App\Models\CallStatus;
use Illuminate\Database\Seeder;

class CallStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = ['draft', 'open', 'closed'];
        foreach ($statuses as $status) {
            CallStatus::updateOrCreate(['name' => $status]);
        }
    }
}
