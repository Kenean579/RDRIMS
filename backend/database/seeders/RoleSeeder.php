<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'super_admin', 'description' => 'System-wide administrator with full access to all tenants.'],
            ['name' => 'research_admin', 'description' => 'University-level research administrator. Manages calls, proposals, and reporting.'],
            ['name' => 'director', 'description' => 'Research center or institute director. Oversees center activities.'],
            ['name' => 'department_head', 'description' => 'Head of an academic department. Approves departmental outputs.'],
            ['name' => 'researcher', 'description' => 'Faculty member or researcher who submits proposals and manages projects.'],
            ['name' => 'reviewer', 'description' => 'Subject matter expert who evaluates proposals and outputs.'],
            ['name' => 'finance_officer', 'description' => 'Reviews proposal budgets and approves expenses.'],
            ['name' => 'ethics_officer', 'description' => 'Manages ethics clearance and IRB approval process.'],
            ['name' => 'student', 'description' => 'Graduate or undergraduate student who submits theses and projects.'],
            ['name' => 'guest', 'description' => 'Default role for newly registered users with limited access.'],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}