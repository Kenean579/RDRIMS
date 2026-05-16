<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use App\Enums\UserRole;

class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Roles from Enum
        foreach (UserRole::cases() as $role) {
            Role::firstOrCreate(['name' => $role->value]);
        }

        // 2. Create Admin User
        $adminRole = Role::where('name', UserRole::SUPER_ADMIN->value)->first();
        $admin = User::firstOrCreate(
            ['email' => 'admin@wollouniversity.edu.et'],
            [
                'name' => 'System Administrator',
                'password' => \Hash::make('Admin@2025!'),
                'is_active' => true
            ]
        );
        $admin->roles()->syncWithoutDetaching([$adminRole->id]);
    }
}
