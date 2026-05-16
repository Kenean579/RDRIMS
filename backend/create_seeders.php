<?php

$seeders = [
    'DatabaseSeeder.php' => <<<'EOT'
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Phase 1: Academic structure (no foreign key dependencies)
            UniversitySeeder::class,
            CampusSeeder::class,
            FacultySeeder::class,
            DepartmentSeeder::class,
            ResearchCenterSeeder::class,
            AcademicYearSeeder::class,

            // Phase 2: Core security (roles and permissions)
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            CenterRoleSeeder::class,

            // Phase 3: Create users (needs departments, roles)
            SuperAdminSeeder::class,

            // Phase 4: All 21 lookup tables (alphabetical order)
            AgreementTypeSeeder::class,
            CallStatusSeeder::class,
            CommunityProblemStatusSeeder::class,
            DetectionServiceSeeder::class,
            DetectionStatusSeeder::class,
            EthicsApprovalStatusSeeder::class,
            FinanceCheckStatusSeeder::class,
            InvitationStatusSeeder::class,
            InvestigatorRoleSeeder::class,
            MilestoneStatusSeeder::class,
            OutputCategorySeeder::class,
            OutputStatusSeeder::class,
            OutputSubtypeSeeder::class,
            ParticipantTypeSeeder::class,
            PatentStatusSeeder::class,
            ProjectStatusSeeder::class,
            ProposalStatusSeeder::class,
            ProposalTypeSeeder::class,
            ReviewDecisionSeeder::class,
            StudentLevelSeeder::class,
            TaskStatusSeeder::class,

            // Phase 5: Core configuration
            ExpertiseSeeder::class,
            ReviewCriteriaSeeder::class,
            SettingSeeder::class,

            // Phase 6: Sample data for testing
            SampleUserSeeder::class,
            UserExpertiseSeeder::class,
        ]);
    }
}
EOT,
    'UniversitySeeder.php' => <<<'EOT'
<?php

namespace Database\Seeders;

use App\Models\University;
use Illuminate\Database\Seeder;

class UniversitySeeder extends Seeder
{
    public function run(): void
    {
        University::create([
            'name' => 'Wollo University',
            'code' => 'WU',
        ]);

        University::create([
            'name' => 'Addis Ababa University',
            'code' => 'AAU',
        ]);

        University::create([
            'name' => 'Bahir Dar University',
            'code' => 'BDU',
        ]);
    }
}
EOT,
    'CampusSeeder.php' => <<<'EOT'
<?php

namespace Database\Seeders;

use App\Models\Campus;
use Illuminate\Database\Seeder;

class CampusSeeder extends Seeder
{
    public function run(): void
    {
        // Wollo University (id=1)
        Campus::create(['name' => 'Dessie Campus', 'code' => 'WU-DESSIE', 'university_id' => 1]);
        Campus::create(['name' => 'Kombolcha Campus', 'code' => 'WU-KOMBOLCHA', 'university_id' => 1]);

        // Addis Ababa University (id=2)
        Campus::create(['name' => 'Siddist Kilo Campus', 'code' => 'AAU-SK', 'university_id' => 2]);
        Campus::create(['name' => 'Arat Kilo Campus', 'code' => 'AAU-AK', 'university_id' => 2]);
        Campus::create(['name' => 'Amist Kilo Campus', 'code' => 'AAU-AMK', 'university_id' => 2]);

        // Bahir Dar University (id=3)
        Campus::create(['name' => 'Main Campus', 'code' => 'BDU-MAIN', 'university_id' => 3]);
        Campus::create(['name' => 'Poly Campus', 'code' => 'BDU-POLY', 'university_id' => 3]);
    }
}
EOT,
    'FacultySeeder.php' => <<<'EOT'
<?php

namespace Database\Seeders;

use App\Models\Faculty;
use Illuminate\Database\Seeder;

class FacultySeeder extends Seeder
{
    public function run(): void
    {
        // Dessie Campus (id=1)
        Faculty::create(['name' => 'Faculty of Natural and Computational Sciences', 'code' => 'WU-DESSIE-FNCS', 'campus_id' => 1]);
        Faculty::create(['name' => 'Faculty of Social Sciences and Humanities', 'code' => 'WU-DESSIE-FSSH', 'campus_id' => 1]);
        Faculty::create(['name' => 'Faculty of Engineering and Technology', 'code' => 'WU-DESSIE-FET', 'campus_id' => 1]);

        // Kombolcha Campus (id=2)
        Faculty::create(['name' => 'Faculty of Business and Economics', 'code' => 'WU-KOM-FBE', 'campus_id' => 2]);
        Faculty::create(['name' => 'Faculty of Health Sciences', 'code' => 'WU-KOM-FHS', 'campus_id' => 2]);
        Faculty::create(['name' => 'Faculty of Agriculture', 'code' => 'WU-KOM-FAG', 'campus_id' => 2]);

        // AAU - Siddist Kilo (id=3)
        Faculty::create(['name' => 'College of Natural Sciences', 'code' => 'AAU-SK-CNS', 'campus_id' => 3]);
        Faculty::create(['name' => 'College of Social Sciences', 'code' => 'AAU-SK-CSS', 'campus_id' => 3]);

        // AAU - Arat Kilo (id=4)
        Faculty::create(['name' => 'College of Law and Governance', 'code' => 'AAU-AK-CLG', 'campus_id' => 4]);
        Faculty::create(['name' => 'College of Education', 'code' => 'AAU-AK-CE', 'campus_id' => 4]);

        // BDU - Main (id=6)
        Faculty::create(['name' => 'Faculty of Electrical and Computer Engineering', 'code' => 'BDU-MAIN-FECE', 'campus_id' => 6]);
        Faculty::create(['name' => 'Faculty of Civil and Water Resources Engineering', 'code' => 'BDU-MAIN-FCWRE', 'campus_id' => 6]);
    }
}
EOT,
    'DepartmentSeeder.php' => <<<'EOT'
<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        // FNCS (id=1)
        Department::create(['name' => 'Computer Science', 'code' => 'CS', 'faculty_id' => 1]);
        Department::create(['name' => 'Information Technology', 'code' => 'IT', 'faculty_id' => 1]);
        Department::create(['name' => 'Physics', 'code' => 'PHYS', 'faculty_id' => 1]);
        Department::create(['name' => 'Chemistry', 'code' => 'CHEM', 'faculty_id' => 1]);
        Department::create(['name' => 'Mathematics', 'code' => 'MATH', 'faculty_id' => 1]);
        Department::create(['name' => 'Biology', 'code' => 'BIO', 'faculty_id' => 1]);

        // FSSH (id=2)
        Department::create(['name' => 'Geography and Environmental Studies', 'code' => 'GEOG', 'faculty_id' => 2]);
        Department::create(['name' => 'History and Heritage Management', 'code' => 'HIST', 'faculty_id' => 2]);
        Department::create(['name' => 'Sociology', 'code' => 'SOC', 'faculty_id' => 2]);
        Department::create(['name' => 'Psychology', 'code' => 'PSY', 'faculty_id' => 2]);

        // FET (id=3)
        Department::create(['name' => 'Electrical and Computer Engineering', 'code' => 'ECE', 'faculty_id' => 3]);
        Department::create(['name' => 'Mechanical Engineering', 'code' => 'ME', 'faculty_id' => 3]);
        Department::create(['name' => 'Civil Engineering', 'code' => 'CE', 'faculty_id' => 3]);

        // FBE (id=4)
        Department::create(['name' => 'Accounting and Finance', 'code' => 'ACFN', 'faculty_id' => 4]);
        Department::create(['name' => 'Management', 'code' => 'MGMT', 'faculty_id' => 4]);
        Department::create(['name' => 'Economics', 'code' => 'ECON', 'faculty_id' => 4]);

        // FHS (id=5)
        Department::create(['name' => 'Public Health', 'code' => 'PH', 'faculty_id' => 5]);
        Department::create(['name' => 'Nursing', 'code' => 'NURS', 'faculty_id' => 5]);
        Department::create(['name' => 'Pharmacy', 'code' => 'PHARM', 'faculty_id' => 5]);

        // FAG (id=6)
        Department::create(['name' => 'Plant Science', 'code' => 'PLSC', 'faculty_id' => 6]);
        Department::create(['name' => 'Animal Science', 'code' => 'ANSC', 'faculty_id' => 6]);
    }
}
EOT,
    'ResearchCenterSeeder.php' => <<<'EOT'
<?php

namespace Database\Seeders;

use App\Models\ResearchCenter;
use Illuminate\Database\Seeder;

class ResearchCenterSeeder extends Seeder
{
    public function run(): void
    {
        ResearchCenter::create([
            'name' => 'ICT and Digital Innovation Research Center',
            'code' => 'ICT-DIRC',
            'parent_university_id' => 1,
            'description' => 'Research in artificial intelligence, cybersecurity, and digital transformation.',
        ]);

        ResearchCenter::create([
            'name' => 'Climate Change and Environmental Research Center',
            'code' => 'CCERC',
            'parent_university_id' => 1,
            'description' => 'Research on climate adaptation, mitigation, and environmental sustainability.',
        ]);

        ResearchCenter::create([
            'name' => 'Public Health and Epidemiology Research Center',
            'code' => 'PHERC',
            'parent_university_id' => 1,
            'description' => 'Community health research, disease surveillance, and health systems strengthening.',
        ]);

        ResearchCenter::create([
            'name' => 'Renewable Energy Research Center',
            'code' => 'RERC',
            'parent_university_id' => 1,
            'description' => 'Solar, wind, and hydro energy research for sustainable development.',
        ]);

        ResearchCenter::create([
            'name' => 'Ethiopian Studies Center',
            'code' => 'AAU-ESC',
            'parent_university_id' => 2,
            'description' => 'Interdisciplinary research on Ethiopian history, culture, and languages.',
        ]);
    }
}
EOT,
    'AcademicYearSeeder.php' => <<<'EOT'
<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        AcademicYear::create([
            'name' => '2024/2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-08-31',
            'is_current' => true,
        ]);

        AcademicYear::create([
            'name' => '2025/2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-08-31',
            'is_current' => false,
        ]);

        AcademicYear::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-08-31',
            'is_current' => false,
        ]);
    }
}
EOT,
    'RoleSeeder.php' => <<<'EOT'
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
EOT,
    'PermissionSeeder.php' => <<<'EOT'
<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // User management
            ['name' => 'manage_users', 'description' => 'Create, update, deactivate, and delete user accounts.'],
            ['name' => 'view_users', 'description' => 'View user profiles and lists.'],

            // Role management
            ['name' => 'manage_roles', 'description' => 'Create, update, and delete roles and permissions.'],
            ['name' => 'view_roles', 'description' => 'View roles and their permissions.'],

            // Academic hierarchy
            ['name' => 'manage_universities', 'description' => 'Create, update, and delete university records.'],
            ['name' => 'manage_campuses', 'description' => 'Create, update, and delete campus records.'],
            ['name' => 'manage_faculties', 'description' => 'Create, update, and delete faculty records.'],
            ['name' => 'manage_departments', 'description' => 'Create, update, and delete department records.'],

            // Research calls
            ['name' => 'create_calls', 'description' => 'Publish calls for research proposals.'],
            ['name' => 'manage_calls', 'description' => 'Update and close research calls.'],

            // Proposals
            ['name' => 'submit_proposals', 'description' => 'Submit research proposals.'],
            ['name' => 'view_all_proposals', 'description' => 'View all proposals across departments.'],
            ['name' => 'approve_proposals', 'description' => 'Approve or reject research proposals.'],
            ['name' => 'assign_reviewers', 'description' => 'Assign reviewers to proposals.'],

            // Reviews
            ['name' => 'write_reviews', 'description' => 'Submit reviews and scores for proposals.'],
            ['name' => 'view_all_reviews', 'description' => 'View all reviews across proposals.'],

            // Finance
            ['name' => 'check_budgets', 'description' => 'Perform budget checks on proposals.'],
            ['name' => 'approve_expenses', 'description' => 'Approve project expenses.'],

            // Ethics
            ['name' => 'approve_ethics', 'description' => 'Approve or reject ethics clearance requests.'],

            // Files
            ['name' => 'upload_files', 'description' => 'Upload files to the repository.'],
            ['name' => 'delete_files', 'description' => 'Delete files from the repository.'],

            // Projects
            ['name' => 'manage_projects', 'description' => 'Create, update, and manage research projects.'],
            ['name' => 'view_all_projects', 'description' => 'View all projects across the university.'],

            // Outputs
            ['name' => 'manage_outputs', 'description' => 'Create and manage research outputs.'],
            ['name' => 'approve_outputs', 'description' => 'Approve theses, dissertations, and other outputs.'],

            // Patents
            ['name' => 'manage_patents', 'description' => 'Register and manage patents and IP.'],

            // Partners
            ['name' => 'manage_partners', 'description' => 'Manage industry and academic partners.'],

            // Events
            ['name' => 'manage_events', 'description' => 'Create and manage workshops, seminars, and conferences.'],

            // Publications
            ['name' => 'manage_publications', 'description' => 'Record and manage publications.'],

            // Reports
            ['name' => 'generate_reports', 'description' => 'Generate system reports and analytics.'],

            // Settings
            ['name' => 'manage_settings', 'description' => 'Modify system-wide settings.'],

            // Community
            ['name' => 'manage_community_problems', 'description' => 'Manage community-submitted problems.'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}
EOT,
    'RolePermissionSeeder.php' => <<<'EOT'
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
EOT,
    'CenterRoleSeeder.php' => <<<'EOT'
<?php

namespace Database\Seeders;

use App\Models\CenterRole;
use Illuminate\Database\Seeder;

class CenterRoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'Director',
            'Deputy Director',
            'Senior Researcher',
            'Researcher',
            'Research Assistant',
            'Administrative Staff',
        ];

        foreach ($roles as $role) {
            CenterRole::create(['name' => $role]);
        }
    }
}
EOT,
    'SuperAdminSeeder.php' => <<<'EOT'
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
EOT,
    'AgreementTypeSeeder.php' => <<<'EOT'
<?php

namespace Database\Seeders;

use App\Models\AgreementType;
use Illuminate\Database\Seeder;

class AgreementTypeSeeder extends Seeder
{
    public function run(): void
    {
        AgreementType::create(['name' => 'mo_u']);
        AgreementType::create(['name' => 'license']);
    }
}
EOT,
    'CallStatusSeeder.php' => <<<'EOT'
<?php

namespace Database\Seeders;

use App\Models\CallStatus;
use Illuminate\Database\Seeder;

class CallStatusSeeder extends Seeder
{
    public function run(): void
    {
        CallStatus::create(['name' => 'draft']);
        CallStatus::create(['name' => 'open']);
        CallStatus::create(['name' => 'closed']);
    }
}
EOT,
    'CommunityProblemStatusSeeder.php' => <<<'EOT'
<?php

namespace Database\Seeders;

use App\Models\CommunityProblemStatus;
use Illuminate\Database\Seeder;

class CommunityProblemStatusSeeder extends Seeder
{
    public function run(): void
    {
        CommunityProblemStatus::create(['name' => 'open']);
        CommunityProblemStatus::create(['name' => 'claimed']);
        CommunityProblemStatus::create(['name' => 'completed']);
    }
}
EOT,
    'DetectionServiceSeeder.php' => <<<'EOT'
<?php

namespace Database\Seeders;

use App\Models\DetectionService;
use Illuminate\Database\Seeder;

class DetectionServiceSeeder extends Seeder
{
    public function run(): void
    {
        DetectionService::create(['name' => 'turnitin']);
        DetectionService::create(['name' => 'copyleaks']);
        DetectionService::create(['name' => 'gptzero']);
        DetectionService::create(['name' => 'local_similarity']);
    }
}
EOT,
    'DetectionStatusSeeder.php' => <<<'EOT'
<?php

namespace Database\Seeders;

use App\Models\DetectionStatus;
use Illuminate\Database\Seeder;

class DetectionStatusSeeder extends Seeder
{
    public function run(): void
    {
        DetectionStatus::create(['name' => 'pending']);
        DetectionStatus::create(['name' => 'processing']);
        DetectionStatus::create(['name' => 'completed']);
        DetectionStatus::create(['name' => 'failed']);
    }
}
EOT,
    'EthicsApprovalStatusSeeder.php' => <<<'EOT'
<?php

namespace Database\Seeders;

use App\Models\EthicsApprovalStatus;
use Illuminate\Database\Seeder;

class EthicsApprovalStatusSeeder extends Seeder
{
    public function run(): void
    {
        EthicsApprovalStatus::create(['name' => 'pending']);
        EthicsApprovalStatus::create(['name' => 'approved']);
        EthicsApprovalStatus::create(['name' => 'rejected']);
    }
}
EOT,
    'FinanceCheckStatusSeeder.php' => <<<'EOT'
<?php

namespace Database\Seeders;

use App\Models\FinanceCheckStatus;
use Illuminate\Database\Seeder;

class FinanceCheckStatusSeeder extends Seeder
{
    public function run(): void
    {
        FinanceCheckStatus::create(['name' => 'pending']);
        FinanceCheckStatus::create(['name' => 'approved']);
        FinanceCheckStatus::create(['name' => 'rejected']);
    }
}
EOT,
    'InvitationStatusSeeder.php' => <<<'EOT'
<?php

namespace Database\Seeders;

use App\Models\InvitationStatus;
use Illuminate\Database\Seeder;

class InvitationStatusSeeder extends Seeder
{
    public function run(): void
    {
        InvitationStatus::create(['name' => 'pending']);
        InvitationStatus::create(['name' => 'accepted']);
        InvitationStatus::create(['name' => 'declined']);
    }
}
EOT,
    'InvestigatorRoleSeeder.php' => <<<'EOT'
<?php

namespace Database\Seeders;

use App\Models\InvestigatorRole;
use Illuminate\Database\Seeder;

class InvestigatorRoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['Lead author', 'Co-author', 'Consultant', 'Mentor', 'Supervisor'];
        foreach ($roles as $role) {
            InvestigatorRole::create(['name' => $role]);
        }
    }
}
EOT,
    'MilestoneStatusSeeder.php' => <<<'EOT'
<?php

namespace Database\Seeders;

use App\Models\MilestoneStatus;
use Illuminate\Database\Seeder;

class MilestoneStatusSeeder extends Seeder
{
    public function run(): void
    {
        MilestoneStatus::create(['name' => 'pending']);
        MilestoneStatus::create(['name' => 'done']);
    }
}
EOT,
    'OutputCategorySeeder.php' => <<<'EOT'
<?php

namespace Database\Seeders;

use App\Models\OutputCategory;
use Illuminate\Database\Seeder;

class OutputCategorySeeder extends Seeder
{
    public function run(): void
    {
        OutputCategory::create(['name' => 'research_center']);
        OutputCategory::create(['name' => 'student']);
    }
}
EOT,
    'OutputStatusSeeder.php' => <<<'EOT'
<?php

namespace Database\Seeders;

use App\Models\OutputStatus;
use Illuminate\Database\Seeder;

class OutputStatusSeeder extends Seeder
{
    public function run(): void
    {
        OutputStatus::create(['name' => 'draft']);
        OutputStatus::create(['name' => 'submitted']);
        OutputStatus::create(['name' => 'approved_by_supervisor']);
        OutputStatus::create(['name' => 'approved']);
        OutputStatus::create(['name' => 'rejected']);
    }
}
EOT,
    'OutputSubtypeSeeder.php' => <<<'EOT'
<?php

namespace Database\Seeders;

use App\Models\OutputSubtype;
use Illuminate\Database\Seeder;

class OutputSubtypeSeeder extends Seeder
{
    public function run(): void
    {
        $subtypes = ['internship', 'final_year_project', 'semester_project', 'thesis', 'research_paper', 'dataset', 'report', 'patent'];
        foreach ($subtypes as $subtype) {
            OutputSubtype::create(['name' => $subtype]);
        }
    }
}
EOT,
    'ParticipantTypeSeeder.php' => <<<'EOT'
<?php

namespace Database\Seeders;

use App\Models\ParticipantType;
use Illuminate\Database\Seeder;

class ParticipantTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['student', 'co_student', 'supervisor', 'co_supervisor', 'advisor'];
        foreach ($types as $type) {
            ParticipantType::create(['name' => $type]);
        }
    }
}
EOT,
    'PatentStatusSeeder.php' => <<<'EOT'
<?php

namespace Database\Seeders;

use App\Models\PatentStatus;
use Illuminate\Database\Seeder;

class PatentStatusSeeder extends Seeder
{
    public function run(): void
    {
        PatentStatus::create(['name' => 'pending']);
        PatentStatus::create(['name' => 'granted']);
        PatentStatus::create(['name' => 'expired']);
    }
}
EOT,
    'ProjectStatusSeeder.php' => <<<'EOT'
<?php

namespace Database\Seeders;

use App\Models\ProjectStatus;
use Illuminate\Database\Seeder;

class ProjectStatusSeeder extends Seeder
{
    public function run(): void
    {
        ProjectStatus::create(['name' => 'active']);
        ProjectStatus::create(['name' => 'completed']);
        ProjectStatus::create(['name' => 'suspended']);
    }
}
EOT,
    'ProposalStatusSeeder.php' => <<<'EOT'
<?php

namespace Database\Seeders;

use App\Models\ProposalStatus;
use Illuminate\Database\Seeder;

class ProposalStatusSeeder extends Seeder
{
    public function run(): void
    {
        ProposalStatus::create(['name' => 'draft']);
        ProposalStatus::create(['name' => 'submitted']);
        ProposalStatus::create(['name' => 'under_review']);
        ProposalStatus::create(['name' => 'finance_check']);
        ProposalStatus::create(['name' => 'approved']);
        ProposalStatus::create(['name' => 'rejected']);
    }
}
EOT,
    'ProposalTypeSeeder.php' => <<<'EOT'
<?php

namespace Database\Seeders;

use App\Models\ProposalType;
use Illuminate\Database\Seeder;

class ProposalTypeSeeder extends Seeder
{
    public function run(): void
    {
        ProposalType::create(['name' => 'sr']);   // Small Research
        ProposalType::create(['name' => 'sp']);   // Strategic Project
        ProposalType::create(['name' => 'thesis']);
    }
}
EOT,
    'ReviewDecisionSeeder.php' => <<<'EOT'
<?php

namespace Database\Seeders;

use App\Models\ReviewDecision;
use Illuminate\Database\Seeder;

class ReviewDecisionSeeder extends Seeder
{
    public function run(): void
    {
        ReviewDecision::create(['name' => 'accept']);
        ReviewDecision::create(['name' => 'minor']);
        ReviewDecision::create(['name' => 'major']);
        ReviewDecision::create(['name' => 'reject']);
    }
}
EOT,
    'StudentLevelSeeder.php' => <<<'EOT'
<?php

namespace Database\Seeders;

use App\Models\StudentLevel;
use Illuminate\Database\Seeder;

class StudentLevelSeeder extends Seeder
{
    public function run(): void
    {
        StudentLevel::create(['name' => 'undergraduate']);
        StudentLevel::create(['name' => 'graduate']);
        StudentLevel::create(['name' => 'phd']);
    }
}
EOT,
    'TaskStatusSeeder.php' => <<<'EOT'
<?php

namespace Database\Seeders;

use App\Models\TaskStatus;
use Illuminate\Database\Seeder;

class TaskStatusSeeder extends Seeder
{
    public function run(): void
    {
        TaskStatus::create(['name' => 'not_started']);
        TaskStatus::create(['name' => 'in_progress']);
        TaskStatus::create(['name' => 'done']);
    }
}
EOT,
    'ExpertiseSeeder.php' => <<<'EOT'
<?php

namespace Database\Seeders;

use App\Models\Expertise;
use Illuminate\Database\Seeder;

class ExpertiseSeeder extends Seeder
{
    public function run(): void
    {
        $expertiseList = [
            'Artificial Intelligence',
            'Machine Learning',
            'Deep Learning',
            'Natural Language Processing',
            'Computer Vision',
            'Data Science',
            'Big Data Analytics',
            'Cloud Computing',
            'Cybersecurity',
            'Blockchain Technology',
            'Internet of Things',
            'Software Engineering',
            'Mobile Application Development',
            'Climate Change Adaptation',
            'Renewable Energy',
            'Solar Energy',
            'Wind Energy',
            'Hydropower',
            'Environmental Science',
            'Water Resource Management',
            'Public Health',
            'Epidemiology',
            'Health Systems Strengthening',
            'Maternal and Child Health',
            'Nutrition',
            'Agriculture',
            'Crop Science',
            'Soil Science',
            'Agricultural Economics',
            'Food Security',
            'Economics',
            'Development Economics',
            'Macroeconomics',
            'Education',
            'Curriculum Development',
            'Gender Studies',
            'Sociology',
            'Psychology',
            'Disaster Risk Management',
            'Urban Planning',
            'Transportation Engineering',
            'Structural Engineering',
            'Material Science',
            'Biotechnology',
            'Pharmaceutical Sciences',
            'Mathematics',
            'Statistics',
        ];

        foreach ($expertiseList as $expertise) {
            Expertise::create(['name' => $expertise]);
        }
    }
}
EOT,
    'ReviewCriteriaSeeder.php' => <<<'EOT'
<?php

namespace Database\Seeders;

use App\Models\ReviewCriterion;
use Illuminate\Database\Seeder;

class ReviewCriteriaSeeder extends Seeder
{
    public function run(): void
    {
        $criteria = [
            [
                'name' => 'Originality',
                'description' => 'Novelty and uniqueness of the research idea. Does it address a knowledge gap?',
                'max_score' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Feasibility',
                'description' => 'Can the research be completed with the proposed resources, timeline, and methodology?',
                'max_score' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Methodology',
                'description' => 'Soundness, appropriateness, and rigor of the research design and methods.',
                'max_score' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Impact',
                'description' => 'Potential societal, academic, economic, or policy impact of the research findings.',
                'max_score' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Budget Justification',
                'description' => 'Is the requested budget reasonable, well-justified, and aligned with the activities?',
                'max_score' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Literature Review',
                'description' => 'Comprehensiveness, currency, and relevance of the literature review and theoretical framework.',
                'max_score' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Ethical Considerations',
                'description' => 'Adequacy of ethical safeguards, informed consent, and data protection measures.',
                'max_score' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Team Qualification',
                'description' => 'Qualifications, experience, and track record of the research team.',
                'max_score' => 10,
                'is_active' => true,
            ],
        ];

        foreach ($criteria as $criterion) {
            ReviewCriterion::create($criterion);
        }
    }
}
EOT,
    'SettingSeeder.php' => <<<'EOT'
<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'app_name', 'value' => 'RDRIMS', 'description' => 'Application name displayed in the UI and emails.'],
            ['key' => 'default_language', 'value' => 'en', 'description' => 'Default system language (en or am).'],
            ['key' => 'max_proposal_budget', 'value' => '5000000', 'description' => 'Maximum allowed budget for a single proposal in ETB.'],
            ['key' => 'min_proposal_budget', 'value' => '10000', 'description' => 'Minimum allowed budget for a proposal in ETB.'],
            ['key' => 'allow_public_registration', 'value' => 'true', 'description' => 'Whether new users can self-register.'],
            ['key' => 'require_email_verification', 'value' => 'false', 'description' => 'Whether users must verify their email before login.'],
            ['key' => 'ethics_required', 'value' => 'true', 'description' => 'Whether ethics clearance is mandatory before proposal approval.'],
            ['key' => 'plagiarism_threshold', 'value' => '20', 'description' => 'Maximum allowed similarity percentage (0-100).'],
            ['key' => 'auto_approve_below_budget', 'value' => '100000', 'description' => 'Proposals below this amount (ETB) skip finance check.'],
            ['key' => 'default_project_duration_months', 'value' => '12', 'description' => 'Default project duration in months.'],
            ['key' => 'max_reviewers_per_proposal', 'value' => '5', 'description' => 'Maximum number of reviewers per proposal.'],
            ['key' => 'min_reviewers_per_proposal', 'value' => '2', 'description' => 'Minimum number of reviewers required per proposal.'],
            ['key' => 'proposal_review_deadline_days', 'value' => '14', 'description' => 'Days reviewers have to submit their review.'],
            ['key' => 'max_file_upload_size_mb', 'value' => '10', 'description' => 'Maximum file upload size in megabytes.'],
            ['key' => 'allowed_file_types', 'value' => 'pdf,doc,docx,xlsx,csv,jpg,png', 'description' => 'Comma-separated list of allowed file extensions.'],
            ['key' => 'enable_notifications', 'value' => 'true', 'description' => 'Whether email/SMS notifications are enabled globally.'],
            ['key' => 'smtp_host', 'value' => 'smtp.gmail.com', 'description' => 'SMTP server hostname.'],
            ['key' => 'smtp_port', 'value' => '587', 'description' => 'SMTP server port.'],
            ['key' => 'sender_email', 'value' => 'noreply@rdrims.local', 'description' => 'From address for system emails.'],
            ['key' => 'sender_name', 'value' => 'RDRIMS Platform', 'description' => 'From name for system emails.'],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}
EOT,
    'SampleUserSeeder.php' => <<<'EOT'
<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SampleUserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // Researchers
            [
                'name' => 'Dr. Tigist Haile',
                'email' => 'tigist.researcher@wollo.edu.et',
                'role' => 'researcher',
                'department_id' => 1,
                'orcid_id' => '0000-0001-1111-2222',
                'bio' => 'Associate Professor of Computer Science. Research interests in AI and NLP.',
            ],
            [
                'name' => 'Dr. Henok Tesfaye',
                'email' => 'henok.researcher@wollo.edu.et',
                'role' => 'researcher',
                'department_id' => 3,
                'orcid_id' => '0000-0001-3333-4444',
                'bio' => 'Assistant Professor of Physics. Research in renewable energy.',
            ],
            [
                'name' => 'Dr. Sara Mohammed',
                'email' => 'sara.researcher@wollo.edu.et',
                'role' => 'researcher',
                'department_id' => 17,
                'orcid_id' => '0000-0001-5555-6666',
                'bio' => 'Lecturer in Public Health. Research in maternal and child health.',
            ],

            // Reviewers
            [
                'name' => 'Prof. Yonas Mulugeta',
                'email' => 'yonas.reviewer@wollo.edu.et',
                'role' => 'reviewer',
                'department_id' => 1,
                'bio' => 'Professor of Computer Science. Expert in AI and data science.',
            ],
            [
                'name' => 'Dr. Frehiwot Assefa',
                'email' => 'frehiwot.reviewer@wollo.edu.et',
                'role' => 'reviewer',
                'department_id' => 7,
                'bio' => 'Associate Professor of Geography. Expert in climate change research.',
            ],
            [
                'name' => 'Dr. Daniel Bekele',
                'email' => 'daniel.reviewer@wollo.edu.et',
                'role' => 'reviewer',
                'department_id' => 11,
                'bio' => 'Associate Professor of Mechanical Engineering.',
            ],

            // Finance Officer
            [
                'name' => 'Ato Solomon Tesfaye',
                'email' => 'solomon.finance@wollo.edu.et',
                'role' => 'finance_officer',
                'department_id' => 14,
                'bio' => 'Senior Finance Officer, Research Directorate.',
            ],

            // Ethics Officer
            [
                'name' => 'Dr. Genet Worku',
                'email' => 'genet.ethics@wollo.edu.et',
                'role' => 'ethics_officer',
                'department_id' => 17,
                'bio' => 'Ethics Committee Chair, Wollo University IRB.',
            ],

            // Department Head
            [
                'name' => 'Dr. Worku Gemechu',
                'email' => 'worku.depthead@wollo.edu.et',
                'role' => 'department_head',
                'department_id' => 1,
                'bio' => 'Head, Department of Computer Science.',
            ],

            // Director
            [
                'name' => 'Prof. Meseret Asnake',
                'email' => 'meseret.director@wollo.edu.et',
                'role' => 'director',
                'department_id' => 1,
                'bio' => 'Director, ICT and Digital Innovation Research Center.',
            ],

            // Students
            [
                'name' => 'Blen Alemu',
                'email' => 'blen.student@wollo.edu.et',
                'role' => 'student',
                'department_id' => 1,
                'bio' => 'MSc student in Computer Science.',
            ],
            [
                'name' => 'Dawit Tadesse',
                'email' => 'dawit.student@wollo.edu.et',
                'role' => 'student',
                'department_id' => 17,
                'bio' => 'PhD candidate in Public Health.',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make('Password@123'),
                'department_id' => $userData['department_id'],
                'is_active' => true,
                'orcid_id' => $userData['orcid_id'] ?? null,
                'bio' => $userData['bio'] ?? null,
            ]);

            $role = Role::where('name', $userData['role'])->first();
            $user->roles()->attach($role->id, [
                'assigned_by' => 1, // super admin
                'assigned_at' => now(),
            ]);
        }
    }
}
EOT,
    'UserExpertiseSeeder.php' => <<<'EOT'
<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Expertise;
use Illuminate\Database\Seeder;

class UserExpertiseSeeder extends Seeder
{
    public function run(): void
    {
        // Dr. Tigist Haile (id=3) -> AI, ML, NLP
        $user = User::where('email', 'tigist.researcher@wollo.edu.et')->first();
        if ($user) {
            $user->expertise()->sync(
                Expertise::whereIn('name', [
                    'Artificial Intelligence', 'Machine Learning', 'Natural Language Processing', 'Deep Learning',
                ])->pluck('id')->toArray()
            );
        }

        // Dr. Henok Tesfaye (id=4) -> Renewable Energy, Solar Energy
        $user = User::where('email', 'henok.researcher@wollo.edu.et')->first();
        if ($user) {
            $user->expertise()->sync(
                Expertise::whereIn('name', [
                    'Renewable Energy', 'Solar Energy', 'Physics',
                ])->pluck('id')->toArray()
            );
        }

        // Dr. Sara Mohammed (id=5) -> Public Health, Epidemiology
        $user = User::where('email', 'sara.researcher@wollo.edu.et')->first();
        if ($user) {
            $user->expertise()->sync(
                Expertise::whereIn('name', [
                    'Public Health', 'Epidemiology', 'Maternal and Child Health', 'Nutrition',
                ])->pluck('id')->toArray()
            );
        }

        // Prof. Yonas Mulugeta (id=6) -> AI, Data Science, Cybersecurity
        $user = User::where('email', 'yonas.reviewer@wollo.edu.et')->first();
        if ($user) {
            $user->expertise()->sync(
                Expertise::whereIn('name', [
                    'Artificial Intelligence', 'Data Science', 'Cybersecurity', 'Machine Learning',
                ])->pluck('id')->toArray()
            );
        }

        // Dr. Frehiwot Assefa (id=7) -> Climate Change, Environmental Science
        $user = User::where('email', 'frehiwot.reviewer@wollo.edu.et')->first();
        if ($user) {
            $user->expertise()->sync(
                Expertise::whereIn('name', [
                    'Climate Change Adaptation', 'Environmental Science', 'Water Resource Management',
                ])->pluck('id')->toArray()
            );
        }

        // Dr. Daniel Bekele (id=8) -> Material Science, Structural Engineering
        $user = User::where('email', 'daniel.reviewer@wollo.edu.et')->first();
        if ($user) {
            $user->expertise()->sync(
                Expertise::whereIn('name', [
                    'Material Science', 'Structural Engineering', 'Transportation Engineering',
                ])->pluck('id')->toArray()
            );
        }
    }
}
EOT,
];

foreach ($seeders as $filename => $content) {
    file_put_contents("d:/proje/qelemeda/RDRIMS/backend/database/seeders/{$filename}", $content);
}
echo "Created " . count($seeders) . " seeder files.\n";

