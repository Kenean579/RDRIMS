<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([

            /*
            |--------------------------------------------------------------------------
            | Core System Configuration
            |--------------------------------------------------------------------------
            */

            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            CenterRoleSeeder::class,


            /*
            |--------------------------------------------------------------------------
            | Global Lookup Tables
            |--------------------------------------------------------------------------
            */

            AcademicYearSeeder::class,

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


            /*
            |--------------------------------------------------------------------------
            | Tenant Organization Hierarchy
            |--------------------------------------------------------------------------
            */

            UniversitySeeder::class,
            CampusSeeder::class,
            FacultySeeder::class,
            DepartmentSeeder::class,
            ResearchCenterSeeder::class,


            /*
            |--------------------------------------------------------------------------
            | Users
            |--------------------------------------------------------------------------
            */

            SuperAdminSeeder::class,
            SampleUserSeeder::class,


            /*
            |--------------------------------------------------------------------------
            | Advanced Permission Assignment
            |--------------------------------------------------------------------------
            */

            HierarchicalPermissionsSeeder::class,


            /*
            |--------------------------------------------------------------------------
            | Supporting Data
            |--------------------------------------------------------------------------
            */

            ExpertiseSeeder::class,
            ReviewCriteriaSeeder::class,
            SettingSeeder::class,

        ]);
    }
}
