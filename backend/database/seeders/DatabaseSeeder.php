<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UniversitySeeder::class,
            CampusSeeder::class,
            FacultySeeder::class,
            DepartmentSeeder::class,
            ResearchCenterSeeder::class,
            AcademicYearSeeder::class,

            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            CenterRoleSeeder::class,

            SuperAdminSeeder::class,

            CallStatusSeeder::class,
            ProposalTypeSeeder::class,
            ProposalStatusSeeder::class,
            ReviewDecisionSeeder::class,
            FinanceCheckStatusSeeder::class,
            EthicsApprovalStatusSeeder::class,
            PatentStatusSeeder::class,
            CommunityProblemStatusSeeder::class,
            ProjectStatusSeeder::class,
            MilestoneStatusSeeder::class,
            TaskStatusSeeder::class,
            InvestigatorRoleSeeder::class,
            InvitationStatusSeeder::class,
            AgreementTypeSeeder::class,
            OutputCategorySeeder::class,
            StudentLevelSeeder::class,
            OutputSubtypeSeeder::class,
            DetectionServiceSeeder::class,
            DetectionStatusSeeder::class,
            ParticipantTypeSeeder::class,
            OutputStatusSeeder::class,

            ExpertiseSeeder::class,
            ReviewCriteriaSeeder::class,
            SettingSeeder::class,

            SampleUserSeeder::class,
            UserExpertiseSeeder::class,
        ]);
    }
}
