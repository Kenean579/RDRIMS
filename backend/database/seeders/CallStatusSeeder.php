<?php

namespace Database\Seeders;

use App\Models\CallStatus;
use Illuminate\Database\Seeder;

class CallStatusSeeder extends Seeder
{
    public function run(): void
    {
        CallStatus::firstOrCreate(['name' => 'draft']);
        CallStatus::firstOrCreate(['name' => 'open']);
        CallStatus::firstOrCreate(['name' => 'closed']);
    }
}
