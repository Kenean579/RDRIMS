<?php

namespace Database\Seeders;

use App\Models\CallStatus;
use Illuminate\Database\Seeder;

class CallStatusSeeder extends Seeder
{
    public function run(): void
    {
        CallStatus::create(['name' => 'draft']);
        CallStatus::create(['name' => 'open']);
        CallStatus::create(['name' => 'closed']);
    }
}