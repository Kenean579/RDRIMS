<?php

namespace Database\Seeders;

use App\Models\AgreementType;
use Illuminate\Database\Seeder;

class AgreementTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['mo_u', 'license'];
        foreach ($types as $type) {
            AgreementType::updateOrCreate(['name' => $type]);
        }
    }
}
