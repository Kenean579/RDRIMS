<?php

namespace Database\Seeders;

use App\Models\InvestigatorRole;
use Illuminate\Database\Seeder;

class InvestigatorRoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['Lead author', 'Co-author', 'Consultant', 'Mentor', 'Supervisor'];
        foreach ($roles as $role) {
            InvestigatorRole::updateOrCreate(['name' => $role]);
        }
    }
}
