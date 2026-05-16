<?php

namespace Database\Seeders;

use App\Models\CenterRole;
use Illuminate\Database\Seeder;

class CenterRoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'Director',
            'Deputy Director',
            'Senior Researcher',
            'Researcher',
            'Research Assistant',
            'Administrative Staff',
        ];

        foreach ($roles as $role) {
            CenterRole::create(['name' => $role]);
        }
    }
}