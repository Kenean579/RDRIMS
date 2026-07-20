<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\CallStatus;
use App\Models\Proposal;
use App\Models\ProposalStatus;
use App\Models\ProposalType;
use App\Models\Role;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MultiTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected University $universityA;
    protected University $universityB;
    protected User $adminA;
    protected User $adminB;
    protected User $researcherA;
    protected Call $callA;
    protected Proposal $proposalA;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic statuses
        CallStatus::firstOrCreate(['name' => 'open']);
        ProposalStatus::firstOrCreate(['name' => 'submitted']);
        ProposalType::firstOrCreate(['name' => 'research']);
        AcademicYear::firstOrCreate(['name' => '2026-2027', 'is_current' => true]);

        // Seed roles
        Role::firstOrCreate(['name' => 'research_admin']);
        Role::firstOrCreate(['name' => 'researcher']);

        // Create Universities
        $this->universityA = University::create(['name' => 'Wollo University', 'code' => 'WU', 'location' => 'Dessie']);
        $this->universityB = University::create(['name' => 'Addis Ababa University', 'code' => 'AAU', 'location' => 'Addis Ababa']);

        // Create Users
        $this->adminA = User::create([
            'name' => 'Admin A',
            'email' => 'adminA@test.com',
            'password' => bcrypt('password'),
            'university_id' => $this->universityA->id,
            'is_active' => true,
        ]);
        $this->adminA->roles()->attach(Role::where('name', 'research_admin')->first()->id);

        $this->adminB = User::create([
            'name' => 'Admin B',
            'email' => 'adminB@test.com',
            'password' => bcrypt('password'),
            'university_id' => $this->universityB->id,
            'is_active' => true,
        ]);
        $this->adminB->roles()->attach(Role::where('name', 'research_admin')->first()->id);

        $this->researcherA = User::create([
            'name' => 'Researcher A',
            'email' => 'researcherA@test.com',
            'password' => bcrypt('password'),
            'university_id' => $this->universityA->id,
            'is_active' => true,
        ]);
        $this->researcherA->roles()->attach(Role::where('name', 'researcher')->first()->id);

        // Create Call in University A
        $this->callA = Call::create([
            'title' => 'Wollo Call',
            'description' => 'WU Call Description',
            'deadline' => now()->addDays(10),
            'thematic_areas' => 'General',
            'created_by' => $this->adminA->id,
            'status_id' => CallStatus::first()->id,
            'university_id' => $this->universityA->id,
        ]);

        // Create Proposal in University A
        $this->proposalA = Proposal::create([
            'call_id' => $this->callA->id,
            'type_id' => ProposalType::first()->id,
            'title' => 'Wollo Proposal',
            'abstract' => 'Abstract',
            'objectives' => 'Objectives',
            'methodology' => 'Methodology',
            'keywords' => 'keywords',
            'budget' => 50000,
            'status_id' => ProposalStatus::first()->id,
            'submitted_by' => $this->researcherA->id,
            'submitted_at' => now(),
        ]);
    }

    public function test_admin_a_can_view_call_a_but_admin_b_cannot(): void
    {
        Sanctum::actingAs($this->adminA);
        $response = $this->getJson("/api/calls/{$this->callA->id}");
        $response->assertOk();

        Sanctum::actingAs($this->adminB);
        $response = $this->getJson("/api/calls/{$this->callA->id}");
        $response->assertStatus(403);
    }

    public function test_admin_a_can_view_proposal_a_but_admin_b_cannot(): void
    {
        Sanctum::actingAs($this->adminA);
        $response = $this->getJson("/api/proposals/{$this->proposalA->id}");
        $response->assertOk();

        Sanctum::actingAs($this->adminB);
        $response = $this->getJson("/api/proposals/{$this->proposalA->id}");
        $response->assertStatus(403);
    }

    public function test_admin_b_cannot_update_or_delete_call_a(): void
    {
        Sanctum::actingAs($this->adminB);
        
        $response = $this->putJson("/api/calls/{$this->callA->id}", [
            'title' => 'Hacked title',
            'deadline' => now()->addDays(5)->format('Y-m-d'),
        ]);
        $response->assertStatus(403);

        $response = $this->deleteJson("/api/calls/{$this->callA->id}");
        $response->assertStatus(403);
    }

    public function test_admin_b_cannot_see_proposal_a_in_the_list(): void
    {
        Sanctum::actingAs($this->adminB);
        $response = $this->getJson('/api/proposals');
        $response->assertOk();
        
        $proposalIds = collect($response->json('data'))->pluck('id');
        $this->assertNotContains($this->proposalA->id, $proposalIds);
    }
}
