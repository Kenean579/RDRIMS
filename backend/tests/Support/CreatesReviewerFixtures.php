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
        $university = \App\Models\University::firstOrCreate(
            ['code' => 'TEST'],
            ['name' => 'Test University', 'location' => 'Test Location']
        );

        $this->submitterUser = User::create([
            'name' => 'Submitter',
            'email' => 'submitter-' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'university_id' => $university->id,
            'is_active' => true,
        ]);

        $this->reviewerUser = User::create([
            'name' => 'Reviewer',
            'email' => 'reviewer-' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'university_id' => $university->id,
            'is_active' => true,
        ]);

        $this->adminUser = User::create([
            'name' => 'Research Admin',
            'email' => 'admin-' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'university_id' => $university->id,
            'is_active' => true,
        ]);

        $reviewerRole = Role::firstOrCreate(['name' => 'reviewer']);
        $adminRole = Role::firstOrCreate(['name' => 'research_admin']);

        // Only attach if not already attached
        if (!$this->reviewerUser->roles()->where('role_id', $reviewerRole->id)->exists()) {
            $this->reviewerUser->roles()->attach($reviewerRole->id, [
                'assigned_at' => now(),
            ]);
        }

        if (!$this->adminUser->roles()->where('role_id', $adminRole->id)->exists()) {
            $this->adminUser->roles()->attach($adminRole->id, [
                'assigned_at' => now(),
            ]);
        }

        $this->criterion = ReviewCriterion::firstOrCreate(
            ['name' => 'Originality'],
            [
                'description' => 'Test criterion',
                'max_score' => 10,
                'is_active' => true,
            ]
        );

        $this->decision = ReviewDecision::firstOrCreate(
            ['name' => 'accept'],
            ['name' => 'accept']
        );

        // Create or get proposal
        $proposalType = \App\Models\ProposalType::firstOrCreate(
            ['name' => 'research'],
            ['name' => 'research']
        );

        $proposalStatus = \App\Models\ProposalStatus::firstOrCreate(
            ['name' => 'under_review'],
            ['name' => 'under_review']
        );

        // Find or create proposal with status_id
        $existingProposal = Proposal::where('submitted_by', $this->submitterUser->id)
            ->where('title', 'Test Proposal')
            ->first();
        
        if ($existingProposal) {
            $this->proposal = $existingProposal;
        } else {
            // Use raw insert to bypass guarded fields
            $proposalId = \DB::table('proposals')->insertGetId([
                'call_id' => null,
                'type_id' => $proposalType->id,
                'title' => 'Test Proposal',
                'abstract' => 'Abstract',
                'objectives' => 'Objectives',
                'methodology' => 'Methodology',
                'keywords' => 'ai, health',
                'budget' => 1000,
                'status_id' => $proposalStatus->id,
                'submitted_by' => $this->submitterUser->id,
                'submitted_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->proposal = Proposal::find($proposalId);
        }

        // Attach reviewer if not already attached
        if (!$this->proposal->reviewers()->where('reviewer_id', $this->reviewerUser->id)->exists()) {
            $this->proposal->reviewers()->attach($this->reviewerUser->id, [
                'assigned_by' => $this->adminUser->id,
                'assigned_at' => now(),
                'deadline_at' => now()->addDays(14),  // 14 days from now
            ]);
        }

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
