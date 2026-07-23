<?php

namespace Tests\Support;

use App\Models\Proposal;
use App\Models\ProposalReviewer;
use App\Models\ReviewCriterion;
use App\Models\ReviewDecision;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

trait CreatesReviewerFixtures
{
    protected User $reviewerUser;
    protected User $adminUser;
    protected User $submitterUser;
    protected Proposal $proposal;
    protected ReviewCriterion $criterion;
    protected ReviewDecision $decision;
    protected ProposalReviewer $assignment;

    protected function seedReviewerFixtures(): void
    {
        \DB::table('proposal_types')->truncate();
        \DB::table('proposal_statuses')->truncate();

        \DB::table('proposal_types')->insert([
            'id' => 1,
            'name' => 'research',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('proposal_statuses')->insert([
            'id' => 1,
            'name' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('proposal_statuses')->insert([
            'id' => 2,
            'name' => 'under_review',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $university = \App\Models\University::firstOrCreate(
            ['code' => 'TEST'],
            ['name' => 'Test University', 'location' => 'Test Location']
        );

        $this->submitterUser = User::create([
            'name' => 'Submitter',
            'email' => 'submitter@test.com',
            'password' => Hash::make('password'),
            'university_id' => $university->id,
            'is_active' => true,
        ]);

        $this->reviewerUser = User::create([
            'name' => 'Reviewer',
            'email' => 'reviewer@test.com',
            'password' => Hash::make('password'),
            'university_id' => $university->id,
            'is_active' => true,
        ]);

        $this->adminUser = User::create([
            'name' => 'Research Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'university_id' => $university->id,
            'is_active' => true,
        ]);

        $reviewerRole = Role::firstOrCreate(['name' => 'reviewer']);
        $adminRole = Role::firstOrCreate(['name' => 'research_admin']);

        $this->reviewerUser->roles()->attach($reviewerRole->id, [
            'assigned_at' => now(),
        ]);

        $this->adminUser->roles()->attach($adminRole->id, [
            'assigned_at' => now(),
        ]);

        $this->criterion = ReviewCriterion::create([
            'name' => 'Originality',
            'description' => 'Test criterion',
            'max_score' => 10,
            'is_active' => true,
        ]);

        $this->decision = ReviewDecision::create(['name' => 'accept']);

        $this->proposal = Proposal::create([
            'call_id' => null,
            'type_id' => 1,
            'title' => 'Test Proposal',
            'abstract' => 'Abstract',
            'objectives' => 'Objectives',
            'methodology' => 'Methodology',
            'keywords' => 'ai, health',
            'budget' => 1000,
            'status_id' => 2,
            'submitted_by' => $this->submitterUser->id,
            'submitted_at' => now(),
        ]);

        $this->proposal->reviewers()->attach($this->reviewerUser->id, [
            'assigned_by' => $this->adminUser->id,
            'assigned_at' => now(),
        ]);

        $this->assignment = ProposalReviewer::where('proposal_id', $this->proposal->id)
            ->where('reviewer_id', $this->reviewerUser->id)
            ->firstOrFail();
    }

    protected function validReviewPayload(): array
    {
        return [
            'scores' => [
                [
                    'criterion_id' => $this->criterion->id,
                    'score' => 8,
                    'comments' => 'Good',
                ],
            ],
            'overall_score' => 4.0,
            'overall_comments' => 'Solid proposal',
            'decision_id' => $this->decision->id,
        ];
    }
}
