<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'super_admin', 'description' => 'Platform administrator – creates tenants, manages system settings, roles & permissions. No access to institutional data.'],
            ['name' => 'research_admin', 'description' => 'University-level admin. Full control over one university, its hierarchy, users, proposals, projects, outputs, and reporting.'],
            ['name' => 'campus_admin', 'description' => 'Campus-level admin. Manages faculties and departments within a campus.'],
            ['name' => 'faculty_admin', 'description' => 'Faculty-level admin. Manages departments within a faculty.'],
            ['name' => 'department_head', 'description' => 'Department head. Manages users and student outputs within a department. Final approver for student theses/projects.'],
            ['name' => 'director', 'description' => 'Research center director. Manages center members, proposals, projects, and outputs.'],
            ['name' => 'researcher', 'description' => 'Faculty member / researcher. Submits proposals, manages own projects and outputs.'],
            ['name' => 'reviewer', 'description' => 'Subject matter expert. Reviews assigned proposals (blind review).'],
            ['name' => 'student', 'description' => 'Undergraduate, graduate, or PhD student. Submits theses, internships, and projects.'],
            ['name' => 'guest', 'description' => 'Default role for self-registered users. Limited to browsing public content and submitting community problems.'],
            ['name' => 'finance_officer', 'description' => 'Finance officer. Reviews proposal budgets, approves expenses within their university.'],
            ['name' => 'ethics_officer', 'description' => 'Ethics officer. Reviews ethics clearance requests and approves/rejects within their university.'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate($role);
        }
    }
}
