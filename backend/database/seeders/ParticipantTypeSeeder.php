<?php

namespace Database\Seeders;

use App\Models\ParticipantType;
use Illuminate\Database\Seeder;

class ParticipantTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['student', 'co_student', 'supervisor', 'co_supervisor', 'advisor'];
        foreach ($types as $type) {
            ParticipantType::create(['name' => $type]);
        }
    }
}