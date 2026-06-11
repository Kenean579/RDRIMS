<?php

namespace Database\Seeders;

use App\Models\AgreementType;
use Illuminate\Database\Seeder;

class AgreementTypeSeeder extends Seeder
{
    public function run(): void
    {
        AgreementType::firstOrCreate(['name' => 'mo_u']);
        AgreementType::firstOrCreate(['name' => 'license']);
    }
}
