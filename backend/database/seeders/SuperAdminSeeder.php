<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin (system-wide)
        $superAdmin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@rdrims.local',
            'password' => Hash::make('Admin@123'),
            'department_id' => 1,
            'is_active' => true,
            'bio' => 'System-wide administrator for the RDRIMS platform. Manages all tenant universities.',
        ]);

        $superAdminRole = Role::where('name', 'super_admin')->first();
        $superAdmin->roles()->attach($superAdminRole->id, [
            'assigned_by' => null,
            'assigned_at' => now(),
        ]);

        // Research Admin for Wollo University
        $researchAdmin = User::create([
            'name' => 'Dr. Abebe Kebede',
            'email' => 'research.admin@wollo.edu.et',
            'password' => Hash::make('Admin@123'),
            'department_id' => 1,
            'is_active' => true,
            'orcid_id' => '0000-0002-1234-5678',
            'google_scholar_id' => 'AbebeKebede2024',
            'bio' => 'Research Administrator at Wollo University. Coordinates all research activities.',
        ]);

        $researchAdminRole = Role::where('name', 'research_admin')->first();
        $researchAdmin->roles()->attach($researchAdminRole->id, [
            'assigned_by' => $superAdmin->id,
            'assigned_at' => now(),
        ]);

        // Research Admin for AAU
        $aauAdmin = User::create([
            'name' => 'Dr. Mesfin Tadesse',
            'email' => 'research.admin@aau.edu.et',
            'password' => Hash::make('Admin@123'),
            'department_id' => 20, // will exist if you have AAU departments
            'is_active' => true,
            'bio' => 'Research Administrator at Addis Ababa University.',
        ]);

        $aauAdmin->roles()->attach($researchAdminRole->id, [
            'assigned_by' => $superAdmin->id,
            'assigned_at' => now(),
        ]);
    }
}