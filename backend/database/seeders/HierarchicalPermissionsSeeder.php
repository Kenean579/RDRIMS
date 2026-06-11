<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HierarchicalPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Hierarchy
        $uni = \App\Models\University::firstOrCreate(['code' => 'TEST_UNI'], [
            'name' => 'Hierarchical Test University',
            'location' => 'Digital Lab'
        ]);

        $campus = \App\Models\Campus::firstOrCreate(['code' => 'NORTH_CAMPUS'], [
            'name' => 'North Campus',
            'university_id' => $uni->id
        ]);

        $faculty = \App\Models\Faculty::firstOrCreate(['code' => 'TECH_FACULTY'], [
            'name' => 'Technology Faculty',
            'campus_id' => $campus->id
        ]);

        $dept = \App\Models\Department::firstOrCreate(['name' => 'AI_DEPT'], [
            'code' => 'AI_DEPT',
            'faculty_id' => $faculty->id
        ]);

        $center = \App\Models\ResearchCenter::firstOrCreate(['code' => 'AI_HUB'], [
            'name' => 'AI Innovation Hub',
            'parent_university_id' => $uni->id,
            'parent_campus_id' => $campus->id,
            'parent_faculty_id' => $faculty->id,
            'parent_department_id' => $dept->id,
            'description' => 'A deeply nested research center'
        ]);

        // 2. Clear previous test data for these perms
        $viewPerm = \App\Models\Permission::firstOrCreate(['name' => 'view_proposals'], ['description' => 'Can view own proposals']);
        $allPerm = \App\Models\Permission::firstOrCreate(['name' => 'view_all_proposals'], ['description' => 'Can view all proposals']);
        $submitPerm = \App\Models\Permission::firstOrCreate(['name' => 'submit_proposals'], ['description' => 'Can submit proposals']);

        // 3. Create Global Role
        $role = \App\Models\Role::firstOrCreate(['name' => 'academic_staff_hierarchical'], [
            'description' => 'Test role for hierarchy inheritance'
        ]);
        $role->permissions()->syncWithoutDetaching([$viewPerm->id]);

        // 4. Set Overrides
        // Faculty level: Grant view_all_proposals
        \App\Models\InstitutionRolePermission::updateOrCreate([
            'faculty_id' => $faculty->id,
            'role_id' => $role->id,
            'permission_id' => $allPerm->id
        ], ['granted' => true]);

        // Research Center level: Revoke view_all_proposals, Grant submit_proposals
        \App\Models\InstitutionRolePermission::updateOrCreate([
            'research_center_id' => $center->id,
            'role_id' => $role->id,
            'permission_id' => $allPerm->id
        ], ['granted' => false]);

        \App\Models\InstitutionRolePermission::updateOrCreate([
            'research_center_id' => $center->id,
            'role_id' => $role->id,
            'permission_id' => $submitPerm->id
        ], ['granted' => true]);

        // 5. Create Test User
        $user = \App\Models\User::firstOrCreate(['email' => 'nested_admin@example.com'], [
            'name' => 'Nested Admin',
            'password' => bcrypt('password'),
            'university_id' => $uni->id,
            'department_id' => $dept->id,
            'research_center_id' => $center->id
        ]);
        $user->roles()->syncWithoutDetaching([$role->id => [
            'assigned_at' => now(),
            'assigned_by' => $user->id // Self-assigned for test
        ]]);

        \Illuminate\Support\Facades\Log::info("Hierarchical Testing Data Seeded.");
        \Illuminate\Support\Facades\Log::info("User 'nested_admin@example.com' should have perms: view_proposals (Global), submit_proposals (RC Override).");
        \Illuminate\Support\Facades\Log::info("User should NOT have: view_all_proposals (Revoked at RC level, despite being granted at Faculty level).");
    }
}
