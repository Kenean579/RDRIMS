<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Call;
use App\Models\Proposal;
use App\Models\Project;
use App\Models\Publication;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Get some references
        $researcher = User::whereHas('roles', function($q) {
            $q->where('name', 'researcher');
        })->first() ?? User::find(1);

        // 1. Funding Call
        $call = Call::updateOrCreate(
            ['title' => 'National Technology Innovation Grant 2025'],
            [
                'description' => 'A grant focusing on AI and sustainable innovation frameworks for East African academic institutes.',
                'deadline' => Carbon::now()->addDays(20),
                'thematic_areas' => json_encode(['AI', 'Agriculture']),
                'status_id' => \App\Models\CallStatus::where('name', 'open')->first()->id ?? 1,
                'academic_year_id' => 1,
                'created_by' => 1
            ]
        );

        // 2. Proposal (Draft)
        $proposalDraft = Proposal::updateOrCreate(
            ['title' => 'Evaluating Large Language Models in Amharic Context'],
            [
                'abstract' => 'This proposal seeks to build an internal testbed for linguistic translation models.',
                'objectives' => 'To test LLMs on local languages.',
                'methodology' => 'Data collection -> Model finetuning -> Evaluation.',
                'keywords' => 'AI, NLP, Amharic',
                'budget' => 120000.00,
                'call_id' => $call->id,
                'submitted_by' => $researcher->id,
                'submitted_at' => Carbon::now(),
                'type_id' => 1,
                'status_id' => \App\Models\ProposalStatus::where('name', 'draft')->first()->id ?? 1,
                'academic_year_id' => 1
            ]
        );

        // 3. Proposal (Submitted)
        $approvedProp = Proposal::updateOrCreate(
            ['title' => 'Agricultural Yield Optimization using Drone Imagery'],
            [
                'abstract' => 'Mapping soil constraints natively using multispectral analysis on local farmlands.',
                'objectives' => 'To increase crop yield by 20% over 5 years.',
                'methodology' => 'Drone scouting over 5 districts.',
                'keywords' => 'Agriculture, Drones, ML',
                'budget' => 450000.00,
                'call_id' => $call->id,
                'submitted_by' => $researcher->id,
                'submitted_at' => Carbon::now()->subMonths(4),
                'approved_at' => Carbon::now()->subMonths(3),
                'type_id' => 1,
                'status_id' => \App\Models\ProposalStatus::where('name', 'approved')->first()->id ?? 1,
                'academic_year_id' => 1
            ]
        );

        $reviewer = User::where('email', 'yonas.reviewer@rdrims.local')->first() ?? User::find(2);
        
        // Give Yonas a pending review
        if (!\DB::table('proposal_reviewers')->where(['proposal_id' => $proposalDraft->id, 'reviewer_id' => $reviewer->id])->exists()) {
            \DB::table('proposal_reviewers')->insert([
                'proposal_id' => $proposalDraft->id,
                'reviewer_id' => $reviewer->id,
                'assigned_by' => 1,
                'assigned_at' => Carbon::now()->subDays(2),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        // Give Yonas a completed review
        if (!\DB::table('proposal_reviewers')->where(['proposal_id' => $approvedProp->id, 'reviewer_id' => $reviewer->id])->exists()) {
            \DB::table('proposal_reviewers')->insert([
                'proposal_id' => $approvedProp->id,
                'reviewer_id' => $reviewer->id,
                'assigned_by' => 1,
                'assigned_at' => Carbon::now()->subMonths(4),
                'submitted_at' => Carbon::now()->subMonths(3)->subDays(10),
                'overall_score' => 85.5,
                'overall_comments' => 'Excellent methodology and clear objectives.',
                'decision_id' => 1, // recommended
                'created_at' => Carbon::now()->subMonths(4),
                'updated_at' => Carbon::now()->subMonths(3)->subDays(10),
            ]);
        }

        // 4. Project (Based on approved proposal)
        Project::updateOrCreate(
            ['proposal_id' => $approvedProp->id],
            [
                'title' => 'Agricultural Yield Optimization Project',
                'total_budget' => 450000.00,
                'start_date' => Carbon::now()->subMonths(3),
                'end_date' => Carbon::now()->addMonths(21),
                'status_id' => \App\Models\ProjectStatus::where('name', 'active')->first()->id ?? 1,
                'pi_id' => $researcher->id,
                'academic_year_id' => 1
            ]
        );

        // 5. Another separate active Project
        Project::updateOrCreate(
            ['title' => 'Solar Energy Capacity Building for Rural Clinics'],
            [
                'proposal_id' => null, // Legacy tracking
                'total_budget' => 200000.00,
                'start_date' => Carbon::now()->subMonths(10),
                'end_date' => Carbon::now()->addMonths(2),
                'status_id' => \App\Models\ProjectStatus::where('name', 'active')->first()->id ?? 1,
                'pi_id' => $researcher->id,
                'academic_year_id' => 1
            ]
        );

        // 6. Publications
        Publication::updateOrCreate(
            ['title' => 'Impact of Renewable Grids on Clinic Sustainability'],
            [
                'abstract' => 'Review of the previous PRJ results published globally.',
                'journal_name' => 'African Journal of Renewable Energy',
                'publication_date' => Carbon::now()->subDays(2),
                'doi' => '10.1038/s41586-020-2649-2',
                'access_type_id' => \App\Models\PublicationAccessType::where('name', 'open_access')->first()->id ?? 1,
                'status_id' => \App\Models\PublicationStatus::where('name', 'published')->first()->id ?? 1,
            ]
        );

        // 7. Partners & Collaborators
        \App\Models\Partner::updateOrCreate(
            ['name' => 'Ministry of Technology'],
            [
                'type_id' => \App\Models\AgreementType::first()->id ?? 1,
                'sector' => 'Government',
                'contact_email' => 'tech@gov.local',
                'website_url' => 'https://mint.gov.local',
                'country' => 'Ethiopia',
                'description' => 'Federal agency for technological development',
            ]
        );
        
        \App\Models\Partner::updateOrCreate(
            ['name' => 'SolarGrid Innovations'],
            [
                'type_id' => \App\Models\AgreementType::first()->id ?? 1,
                'sector' => 'Renewable Energy',
                'contact_email' => 'contact@solargrid.local',
                'website_url' => 'https://solargrid.local',
                'country' => 'Kenya',
                'description' => 'Industry partner for green energy solutions',
            ]
        );

        // 8. Finance Checks
        \App\Models\FinanceCheck::updateOrCreate(
            ['proposal_id' => $approvedProp->id],
            [
                'status_id' => \App\Models\FinanceCheckStatus::where('name', 'approved')->first()->id ?? 2,
                'checker_id' => 1,
                'checked_at' => Carbon::now()->subMonths(3),
                'comments' => 'Budget aligns with the grant limits. Approved.',
                'approved_budget' => 450000.00
            ]
        );

        \App\Models\FinanceCheck::updateOrCreate(
            ['proposal_id' => $proposalDraft->id],
            [
                'status_id' => \App\Models\FinanceCheckStatus::where('name', 'pending')->first()->id ?? 1,
                'checker_id' => null,
                'checked_at' => Carbon::now(), // required column based on migration
            ]
        );

        // 9. Community Problems
        \App\Models\CommunityProblem::updateOrCreate(
            ['title' => 'Lack of clean water in southern district schools'],
            [
                'description' => 'Three primary schools in the zone have no access to potable water, leading to health issues among students.',
                'location' => 'Southern Zone, District 4',
                'submitted_by' => $researcher->id,
                'contact_info' => 'director@school.local',
                'status_id' => \App\Models\CommunityProblemStatus::where('name', 'open')->first()->id ?? 1,
                'is_anonymous' => false
            ]
        );

        \App\Models\CommunityProblem::updateOrCreate(
            ['title' => 'Pest outbreak affecting maize crops'],
            [
                'description' => 'Farmers reporting unusual pest damage that is resisting common pesticides.',
                'location' => 'Eastern Farming Cooperative',
                'submitted_by' => null,
                'contact_info' => 'coop.leader@agri.local',
                'status_id' => \App\Models\CommunityProblemStatus::where('name', 'claimed')->first()->id ?? 2,
                'claimed_by' => 1,
                'claimed_at' => Carbon::now()->subDays(5),
                'is_anonymous' => true
            ]
        );
    }

}
