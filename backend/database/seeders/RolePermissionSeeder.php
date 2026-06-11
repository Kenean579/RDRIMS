<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = Role::where('name', 'super_admin')->first();
        $superAdmin->permissions()->sync(Permission::pluck('id')->toArray());

        $researchAdmin = Role::where('name', 'research_admin')->first();
        $researchAdmin->permissions()->sync(
            Permission::whereNotIn('name', ['manage_settings', 'manage_roles', 'manage_universities'])->pluck('id')->toArray()
        );

        $campusAdmin = Role::where('name', 'campus_admin')->first();
        $campusAdmin->permissions()->sync(
            Permission::whereIn('name', [
                'view_users', 'create_calls', 'manage_calls', 'view_all_proposals',
                'approve_proposals', 'assign_reviewers', 'view_all_reviews',
                'manage_projects', 'view_all_projects', 'manage_outputs',
                'approve_outputs', 'manage_publications', 'manage_patents',
                'manage_partners', 'manage_events', 'upload_files', 'delete_files',
                'manage_community_problems', 'generate_reports',
            ])->pluck('id')->toArray()
        );

        $facultyAdmin = Role::where('name', 'faculty_admin')->first();
        $facultyAdmin->permissions()->sync(
            Permission::whereIn('name', [
                'view_users', 'create_calls', 'manage_calls', 'view_all_proposals',
                'approve_proposals', 'assign_reviewers', 'view_all_reviews',
                'view_all_projects', 'manage_outputs', 'approve_outputs',
                'manage_publications', 'manage_patents', 'manage_events',
                'upload_files', 'generate_reports',
            ])->pluck('id')->toArray()
        );

        $deptHead = Role::where('name', 'department_head')->first();
        $deptHead->permissions()->sync(
            Permission::whereIn('name', [
                'view_users', 'view_all_proposals', 'approve_outputs',
                'view_all_projects', 'generate_reports', 'upload_files',
            ])->pluck('id')->toArray()
        );

        $director = Role::where('name', 'director')->first();
        $director->permissions()->sync(
            Permission::whereIn('name', [
                'view_users', 'view_all_proposals', 'view_all_projects',
                'view_all_reviews', 'manage_outputs', 'approve_outputs',
                'manage_publications', 'generate_reports', 'upload_files',
                'manage_events',
            ])->pluck('id')->toArray()
        );

        $researcher = Role::where('name', 'researcher')->first();
        $researcher->permissions()->sync(
            Permission::whereIn('name', [
                'submit_proposals', 'upload_files', 'view_users',
                'manage_projects', 'manage_outputs', 'manage_publications',
                'manage_patents', 'manage_community_problems',
            ])->pluck('id')->toArray()
        );

        $reviewer = Role::where('name', 'reviewer')->first();
        $reviewer->permissions()->sync(
            Permission::whereIn('name', [
                'write_reviews', 'view_all_proposals', 'upload_files', 'view_users',
            ])->pluck('id')->toArray()
        );

        $student = Role::where('name', 'student')->first();
        $student->permissions()->sync(
            Permission::whereIn('name', [
                'upload_files', 'manage_outputs', 'view_users', 'manage_community_problems',
            ])->pluck('id')->toArray()
        );

        $guest = Role::where('name', 'guest')->first();
        $guest->permissions()->sync(
            Permission::whereIn('name', ['view_users'])->pluck('id')->toArray()
        );

        $financeOfficer = Role::where('name', 'finance_officer')->first();
        $financeOfficer->permissions()->sync(
            Permission::whereIn('name', [
                'check_budgets', 'approve_expenses', 'view_all_proposals',
                'view_all_projects', 'generate_reports', 'view_users',
            ])->pluck('id')->toArray()
        );

        $ethicsOfficer = Role::where('name', 'ethics_officer')->first();
        $ethicsOfficer->permissions()->sync(
            Permission::whereIn('name', [
                'approve_ethics', 'view_all_proposals', 'view_users', 'upload_files',
            ])->pluck('id')->toArray()
        );
    }
}
