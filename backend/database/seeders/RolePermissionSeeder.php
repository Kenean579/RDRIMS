<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin gets ALL permissions
        $superAdmin = Role::where('name', 'super_admin')->first();
        $superAdmin->permissions()->sync(Permission::pluck('id')->toArray());

        // Research Admin gets all except system-critical permissions
        $researchAdmin = Role::where('name', 'research_admin')->first();
        $researchAdmin->permissions()->sync(
            Permission::whereNotIn('name', ['manage_settings', 'manage_roles', 'manage_universities'])->pluck('id')->toArray()
        );

        // Director permissions
        $director = Role::where('name', 'director')->first();
        $director->permissions()->sync(
            Permission::whereIn('name', [
                'view_users', 'view_all_proposals', 'view_all_projects',
                'view_all_reviews', 'manage_outputs', 'approve_outputs',
                'manage_publications', 'generate_reports', 'upload_files',
                'manage_events',
            ])->pluck('id')->toArray()
        );

        // Department Head permissions
        $deptHead = Role::where('name', 'department_head')->first();
        $deptHead->permissions()->sync(
            Permission::whereIn('name', [
                'view_users', 'view_all_proposals', 'approve_outputs',
                'view_all_projects', 'generate_reports', 'upload_files',
            ])->pluck('id')->toArray()
        );

        // Researcher permissions
        $researcher = Role::where('name', 'researcher')->first();
        $researcher->permissions()->sync(
            Permission::whereIn('name', [
                'submit_proposals', 'upload_files', 'view_users',
                'manage_projects', 'manage_outputs', 'manage_publications',
                'manage_patents', 'manage_community_problems',
            ])->pluck('id')->toArray()
        );

        // Reviewer permissions
        $reviewer = Role::where('name', 'reviewer')->first();
        $reviewer->permissions()->sync(
            Permission::whereIn('name', [
                'write_reviews', 'view_all_proposals', 'upload_files',
                'view_users',
            ])->pluck('id')->toArray()
        );

        // Finance Officer permissions
        $financeOfficer = Role::where('name', 'finance_officer')->first();
        $financeOfficer->permissions()->sync(
            Permission::whereIn('name', [
                'check_budgets', 'approve_expenses', 'view_all_proposals',
                'view_all_projects', 'generate_reports', 'view_users',
            ])->pluck('id')->toArray()
        );

        // Ethics Officer permissions
        $ethicsOfficer = Role::where('name', 'ethics_officer')->first();
        $ethicsOfficer->permissions()->sync(
            Permission::whereIn('name', [
                'approve_ethics', 'view_all_proposals', 'view_users',
                'upload_files',
            ])->pluck('id')->toArray()
        );

        // Student permissions
        $student = Role::where('name', 'student')->first();
        $student->permissions()->sync(
            Permission::whereIn('name', [
                'upload_files', 'manage_outputs', 'view_users',
                'manage_community_problems',
            ])->pluck('id')->toArray()
        );

        // Guest permissions (minimal)
        $guest = Role::where('name', 'guest')->first();
        $guest->permissions()->sync(
            Permission::whereIn('name', ['view_users'])->pluck('id')->toArray()
        );
    }
}