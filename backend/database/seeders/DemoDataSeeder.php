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
        $call = Call::create([
            'title' => 'National Technology Innovation Grant 2025',
            'description' => 'A grant focusing on AI and sustainable innovation frameworks for East African academic institutes.',
            'deadline' => Carbon::now()->addDays(20),
            'thematic_areas' => json_encode(['AI', 'Agriculture']),
            'status_id' => \App\Models\CallStatus::where('name', 'open')->first()->id ?? 1,
            'academic_year_id' => 1,
            'created_by' => 1
        ]);

        // 2. Proposal (Draft)
        Proposal::create([
            'title' => 'Evaluating Large Language Models in Amharic Context',
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
        ]);

        // 3. Proposal (Submitted)
        $approvedProp = Proposal::create([
            'title' => 'Agricultural Yield Optimization using Drone Imagery',
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
        ]);

        // 4. Project (Based on approved proposal)
        Project::create([
            'proposal_id' => $approvedProp->id,
            'title' => 'Agricultural Yield Optimization Project',
            'total_budget' => 450000.00,
            'start_date' => Carbon::now()->subMonths(3),
            'end_date' => Carbon::now()->addMonths(21),
            'status_id' => \App\Models\ProjectStatus::where('name', 'active')->first()->id ?? 1,
            'pi_id' => $researcher->id,
            'academic_year_id' => 1
        ]);

        // 5. Another separate active Project
        Project::create([
            'proposal_id' => null, // Legacy tracking
            'title' => 'Solar Energy Capacity Building for Rural Clinics',
            'total_budget' => 200000.00,
            'start_date' => Carbon::now()->subMonths(10),
            'end_date' => Carbon::now()->subDays(5),
            'status_id' => \App\Models\ProjectStatus::where('name', 'completed')->first()->id ?? 1,
            'pi_id' => $researcher->id,
            'academic_year_id' => 1
        ]);

        // 6. Publications
        Publication::create([
            'title' => 'Impact of Renewable Grids on Clinic Sustainability',
            'abstract' => 'Review of the previous PRJ results published globally.',
            'journal_name' => 'African Journal of Renewable Energy',
            'publication_date' => Carbon::now()->subDays(2),
            'doi' => '10.1000/xyz123',
            'access_type_id' => \App\Models\PublicationAccessType::where('name', 'open_access')->first()->id ?? 1,
            'status_id' => \App\Models\PublicationStatus::where('name', 'published')->first()->id ?? 1,
        ]);
    }
}
