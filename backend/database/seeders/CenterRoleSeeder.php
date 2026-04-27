<?php

namespace Database\Seeders;

use App\Models\CenterRole;
use Illuminate\Database\Seeder;

class CenterRoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['Director', 'Member', 'Researcher', 'Staff'];
        foreach ($roles as $role) {
            CenterRole::updateOrCreate(['name' => $role]);
        }
    }
}
