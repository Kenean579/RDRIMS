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
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@rdrims.local'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('Admin@123'),
                'university_id' => null,
                'is_active' => true,
                'bio' => 'Platform-wide system administrator. Manages all tenant universities and system configuration.',
            ]
        );
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole && !$superAdmin->roles->contains($superAdminRole->id)) {
            $superAdmin->roles()->attach($superAdminRole->id, ['assigned_by' => null, 'assigned_at' => now()]);
        }

        
        $researchAdmin = User::updateOrCreate(
            ['email' => 'research.admin@wollo.edu.et'],
            [
                'name' => 'Dr. Abebe Kebede',
                'password' => Hash::make('Admin@123'),
                'department_id' => 1,
                'university_id' => 1,
                'is_active' => true,
                'orcid_id' => '0000-0002-1234-5678',
                'bio' => 'Research Administrator at Wollo University. Coordinates all research activities.',
            ]
        );
        $researchAdminRole = Role::where('name', 'research_admin')->first();
        if ($researchAdminRole && !$researchAdmin->roles->contains($researchAdminRole->id)) {
            $researchAdmin->roles()->attach($researchAdminRole->id, ['assigned_by' => $superAdmin->id, 'assigned_at' => now()]);
        }

        $aauAdmin = User::updateOrCreate(
            ['email' => 'research.admin@aau.edu.et'],
            [
                'name' => 'Dr. Mesfin Tadesse',
                'password' => Hash::make('Admin@123'),
                'department_id' => 1,
                'university_id' => 1,
                'is_active' => true,
                'bio' => 'Research Administrator at Addis Ababa University.',
            ]
        );
        if ($researchAdminRole && !$aauAdmin->roles->contains($researchAdminRole->id)) {
            $aauAdmin->roles()->attach($researchAdminRole->id, ['assigned_by' => $superAdmin->id, 'assigned_at' => now()]);
        }
    }
}
