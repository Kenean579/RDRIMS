<?php

namespace Database\Seeders;

use App\Models\ParticipantType;
use Illuminate\Database\Seeder;

class ParticipantTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['student', 'co-student', 'supervisor', 'co-supervisor', 'advisor'];
        foreach ($types as $type) {
            ParticipantType::updateOrCreate(['name' => $type]);
        }
    }
}
