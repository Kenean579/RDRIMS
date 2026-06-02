<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;

class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create default roles from a plain array (no enum needed)
        $defaultRoles = ['super_admin', 'admin', 'reviewer', 'researcher', 'guest'];

        foreach ($defaultRoles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // 2. Create Admin User
        $adminRole = Role::where('name', 'super_admin')->first();
        $admin = User::firstOrCreate(
            ['email' => 'admin@rdrims.local'],
            [
                'name' => 'System Administrator',
                'password' => \Hash::make('Admin@2025!'),
                'is_active' => true
            ]
        );
        $admin->roles()->syncWithoutDetaching([$adminRole->id]);
    }
}
