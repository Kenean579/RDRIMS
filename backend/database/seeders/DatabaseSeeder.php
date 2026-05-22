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
            EventStatusSeeder::class,
            ExpenseCategorySeeder::class,
            ExpenseStatusSeeder::class,
            FinanceCheckStatusSeeder::class,
            InvitationStatusSeeder::class,
            InvestigatorRoleSeeder::class,
            LocaleSeeder::class,
            MilestoneStatusSeeder::class,
            OutputCategorySeeder::class,
            OutputStatusSeeder::class,
            OutputSubtypeSeeder::class,
            ParticipantTypeSeeder::class,
            PatentStatusSeeder::class,
            ProjectStatusSeeder::class,
            ProposalStatusSeeder::class,
            ProposalTypeSeeder::class,
            PublicationAccessTypeSeeder::class,
            PublicationStatusSeeder::class,
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