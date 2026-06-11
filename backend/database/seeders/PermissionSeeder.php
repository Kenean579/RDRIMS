<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'manage_users', 'description' => 'Create, update, deactivate, and delete user accounts.'],
            ['name' => 'view_users', 'description' => 'View user profiles and lists.'],
            ['name' => 'manage_roles', 'description' => 'Create, update, and delete roles and permissions.'],
            ['name' => 'view_roles', 'description' => 'View roles and their permissions.'],
            ['name' => 'manage_universities', 'description' => 'Create, update, and delete university records.'],
            ['name' => 'manage_campuses', 'description' => 'Create, update, and delete campus records.'],
            ['name' => 'manage_faculties', 'description' => 'Create, update, and delete faculty records.'],
            ['name' => 'manage_departments', 'description' => 'Create, update, and delete department records.'],
            ['name' => 'create_calls', 'description' => 'Publish calls for research proposals.'],
            ['name' => 'manage_calls', 'description' => 'Update and close research calls.'],
            ['name' => 'submit_proposals', 'description' => 'Submit research proposals.'],
            ['name' => 'view_all_proposals', 'description' => 'View all proposals across departments.'],
            ['name' => 'approve_proposals', 'description' => 'Approve or reject research proposals.'],
            ['name' => 'assign_reviewers', 'description' => 'Assign reviewers to proposals.'],
            ['name' => 'write_reviews', 'description' => 'Submit reviews and scores for proposals.'],
            ['name' => 'view_all_reviews', 'description' => 'View all reviews across proposals.'],
            ['name' => 'check_budgets', 'description' => 'Perform budget checks on proposals.'],
            ['name' => 'approve_expenses', 'description' => 'Approve project expenses.'],
            ['name' => 'approve_ethics', 'description' => 'Approve or reject ethics clearance requests.'],
            ['name' => 'upload_files', 'description' => 'Upload files to the repository.'],
            ['name' => 'delete_files', 'description' => 'Delete files from the repository.'],
            ['name' => 'manage_projects', 'description' => 'Create, update, and manage research projects.'],
            ['name' => 'view_all_projects', 'description' => 'View all projects across the university.'],
            ['name' => 'manage_outputs', 'description' => 'Create and manage research outputs.'],
            ['name' => 'approve_outputs', 'description' => 'Approve theses, dissertations, and other outputs.'],
            ['name' => 'manage_patents', 'description' => 'Register and manage patents and IP.'],
            ['name' => 'manage_partners', 'description' => 'Manage industry and academic partners.'],
            ['name' => 'manage_events', 'description' => 'Create and manage workshops, seminars, and conferences.'],
            ['name' => 'manage_publications', 'description' => 'Record and manage research publications.'],
            ['name' => 'generate_reports', 'description' => 'Generate system reports and analytics.'],
            ['name' => 'manage_settings', 'description' => 'Modify system-wide settings.'],
            ['name' => 'manage_community_problems', 'description' => 'Manage community-submitted problems.'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate($permission);
        }
    }
}
