<?php

namespace Database\Seeders;

use App\Models\AgreementType;
use Illuminate\Database\Seeder;

class AgreementTypeSeeder extends Seeder
{
    public function run(): void
    {
        AgreementType::create(['name' => 'mo_u']);
        AgreementType::create(['name' => 'license']);
    }
}