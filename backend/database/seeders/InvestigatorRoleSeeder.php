<?php

namespace Database\Seeders;

use App\Models\InvestigatorRole;
use Illuminate\Database\Seeder;

class InvestigatorRoleSeeder extends Seeder
{
    public function run(): void
    {
        InvestigatorRole::firstOrCreate(['name' => 'Lead author']);
        InvestigatorRole::firstOrCreate(['name' => 'Co-author']);
        InvestigatorRole::firstOrCreate(['name' => 'Consultant']);
        InvestigatorRole::firstOrCreate(['name' => 'Mentor']);
        InvestigatorRole::firstOrCreate(['name' => 'Supervisor']);
    }
}
