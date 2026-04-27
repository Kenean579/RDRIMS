<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CenterRoleSeeder::class,
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
        ]);

        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@rdrims.com',
            'password' => bcrypt('password'),
        ]);
    }
}
