<?php

namespace Tests\Feature\Project;

use App\Models\Milestone;
use App\Models\MilestoneStatus;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\User;
use App\Models\University;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectModuleTest extends TestCase
{
    use RefreshDatabase;

    private $university;
    private $admin;
    private $pi;
    private $projectStatus;
    private $milestoneStatus;

    protected function setUp(): void
    {
        parent::setUp();

        // Create university
        $this->university = University::factory()->create();

        // Create roles
        $researchAdminRole = \App\Models\Role::firstOrCreate(
            ['name' => 'research_admin'],
            ['description' => 'Research Administrator']
        );

        // Create users
        $this->admin = User::factory()->create(['university_id' => $this->university->id]);
        $this->admin->roles()->attach($researchAdminRole->id);

        $this->pi = User::factory()->create(['university_id' => $this->university->id]);

        // Create statuses
        ProjectStatus::firstOrCreate(['name' => 'draft']);
        ProjectStatus::firstOrCreate(['name' => 'planning']);
        ProjectStatus::firstOrCreate(['name' => 'active']);
        ProjectStatus::firstOrCreate(['name' => 'suspended']);
        ProjectStatus::firstOrCreate(['name' => 'completed']);
        ProjectStatus::firstOrCreate(['name' => 'closed']);

        MilestoneStatus::firstOrCreate(['name' => 'pending']);
        MilestoneStatus::firstOrCreate(['name' => 'in_progress']);
        MilestoneStatus::firstOrCreate(['name' => 'completed']);
    }

    public function test_admin_can_create_project(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/projects', [
            'title' => 'AI Research Project',
            'description' => 'Research on machine learning',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'total_budget' => 100000,
            'pi_id' => $this->pi->id,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('projects', [
            'title' => 'AI Research Project',
            'created_by' => $this->admin->id,
        ]);

        // Check history was created
        $this->assertDatabaseHas('project_histories', [
            'action' => 'created',
            'performed_by' => $this->admin->id,
        ]);
    }

    public function test_project_starts_in_draft_status(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/projects', [
            'title' => 'Test Project',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'total_budget' => 10000,
            'pi_id' => $this->pi->id,
        ]);

        $project = Project::latest()->first();
        $this->assertEquals('draft', $project->status->name);
    }

    public function test_pi_can_submit_project_with_milestones(): void
    {
        $project = Project::factory()->create([
            'pi_id' => $this->pi->id,
            'created_by' => $this->pi->id,
            'status_id' => ProjectStatus::where('name', 'draft')->first()->id,
        ]);

        // Add a milestone
        $project->milestones()->create([
            'title' => 'Milestone 1',
            'due_date' => now()->addMonth(),
            'status_id' => MilestoneStatus::where('name', 'pending')->first()->id,
        ]);

        Sanctum::actingAs($this->pi);
        $response = $this->postJson("/api/projects/{$project->id}/submit");

        $response->assertOk();
        $this->assertEquals('planning', $project->fresh()->status->name);
    }

    public function test_cannot_submit_project_without_milestones(): void
    {
        $project = Project::factory()
            ->create([
                'pi_id' => $this->pi->id,
                'created_by' => $this->pi->id,
                'status_id' => ProjectStatus::where('name', 'draft')->first()->id,
            ]);

        Sanctum::actingAs($this->pi);
        $response = $this->postJson("/api/projects/{$project->id}/submit");

        $response->assertUnprocessable();
    }

    public function test_admin_can_approve_project(): void
    {
        $project = Project::factory()
            ->create([
                'pi_id' => $this->pi->id,
                'status_id' => ProjectStatus::where('name', 'planning')->first()->id,
            ]);

        Sanctum::actingAs($this->admin);
        $response = $this->postJson("/api/projects/{$project->id}/approve", [
            'comments' => 'Approved for execution',
        ]);

        $response->assertOk();
        $this->assertEquals('active', $project->fresh()->status->name);
    }

    public function test_admin_can_reject_project(): void
    {
        $project = Project::factory()
            ->create([
                'pi_id' => $this->pi->id,
                'status_id' => ProjectStatus::where('name', 'planning')->first()->id,
            ]);

        Sanctum::actingAs($this->admin);
        $response = $this->postJson("/api/projects/{$project->id}/reject", [
            'reason' => 'Needs more detail',
        ]);

        $response->assertOk();
        $this->assertEquals('draft', $project->fresh()->status->name);
    }

    public function test_pi_can_suspend_active_project(): void
    {
        $project = Project::factory()
            ->create([
                'pi_id' => $this->pi->id,
                'status_id' => ProjectStatus::where('name', 'active')->first()->id,
            ]);

        Sanctum::actingAs($this->pi);
        $response = $this->postJson("/api/projects/{$project->id}/suspend", [
            'reason' => 'Awaiting additional funding',
        ]);

        $response->assertOk();
        $this->assertEquals('suspended', $project->fresh()->status->name);
    }

    public function test_pi_can_reactivate_suspended_project(): void
    {
        $project = Project::factory()
            ->create([
                'pi_id' => $this->pi->id,
                'status_id' => ProjectStatus::where('name', 'suspended')->first()->id,
            ]);

        Sanctum::actingAs($this->pi);
        $response = $this->postJson("/api/projects/{$project->id}/reactivate");

        $response->assertOk();
        $this->assertEquals('active', $project->fresh()->status->name);
    }

    public function test_can_complete_project_with_all_milestones_done(): void
    {
        $project = Project::factory()
            ->create([
                'pi_id' => $this->pi->id,
                'status_id' => ProjectStatus::where('name', 'active')->first()->id,
            ]);

        // Add completed milestone
        $project->milestones()->create([
            'title' => 'Milestone 1',
            'due_date' => now()->addMonth(),
            'status_id' => MilestoneStatus::where('name', 'completed')->first()->id,
        ]);

        Sanctum::actingAs($this->pi);
        $response = $this->postJson("/api/projects/{$project->id}/complete");

        $response->assertOk();
        $this->assertEquals('completed', $project->fresh()->status->name);
    }

    public function test_cannot_complete_project_with_pending_milestones(): void
    {
        $project = Project::factory()
            ->create([
                'pi_id' => $this->pi->id,
                'status_id' => ProjectStatus::where('name', 'active')->first()->id,
            ]);

        // Add pending milestone
        $project->milestones()->create([
            'title' => 'Milestone 1',
            'due_date' => now()->addMonth(),
            'status_id' => MilestoneStatus::where('name', 'pending')->first()->id,
        ]);

        Sanctum::actingAs($this->pi);
        $response = $this->postJson("/api/projects/{$project->id}/complete");

        $response->assertUnprocessable();
    }

    public function test_can_add_investigator_to_project(): void
    {
        $project = Project::factory()
            ->create([
                'pi_id' => $this->pi->id,
            ]);

        $investigator = User::factory()->create(['university_id' => $this->university->id]);

        Sanctum::actingAs($this->pi);
        $response = $this->postJson("/api/projects/{$project->id}/investigators", [
            'user_id' => $investigator->id,
            'role' => 'co_pi',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('project_investigators', [
            'project_id' => $project->id,
            'user_id' => $investigator->id,
            'role' => 'co_pi',
        ]);
    }

    public function test_can_get_project_progress_stats(): void
    {
        $project = Project::factory()
            ->create([
                'pi_id' => $this->pi->id,
            ]);

        $project->milestones()->create([
            'title' => 'Milestone 1',
            'due_date' => now()->addMonth(),
            'status_id' => MilestoneStatus::where('name', 'completed')->first()->id,
        ]);

        $project->milestones()->create([
            'title' => 'Milestone 2',
            'due_date' => now()->addMonth(),
            'status_id' => MilestoneStatus::where('name', 'pending')->first()->id,
        ]);

        Sanctum::actingAs($this->pi);
        $response = $this->getJson("/api/projects/{$project->id}/progress");

        $response->assertOk();
        $response->assertJsonStructure([
            'total_milestones',
            'completed_milestones',
            'pending_milestones',
            'progress_percentage',
        ]);

        $this->assertEquals(2, $response->json('total_milestones'));
        $this->assertEquals(1, $response->json('completed_milestones'));
        $this->assertEquals(50.0, $response->json('progress_percentage'));
    }

    public function test_can_get_budget_statistics(): void
    {
        $project = Project::factory()
            ->create([
                'pi_id' => $this->pi->id,
                'total_budget' => 100000,
            ]);

        // Add approved expense
        $project->expenses()->create([
            'amount' => 25000,
            'budget_category' => 'Personnel',
            'description' => 'Salaries',
            'approved_by' => $this->admin->id,
            'approved_at' => now(),
        ]);

        Sanctum::actingAs($this->admin);
        $response = $this->getJson("/api/projects/{$project->id}/budget-stats");

        $response->assertOk();
        $response->assertJsonStructure([
            'total_budget',
            'total_expenses',
            'remaining_budget',
            'budget_utilization_percentage',
        ]);

        $this->assertEquals(100000, $response->json('total_budget'));
        $this->assertEquals(25000, $response->json('total_expenses'));
        $this->assertEquals(75000, $response->json('remaining_budget'));
    }

    public function test_can_list_projects_with_pagination(): void
    {
        Project::factory(5)
            ->create(['pi_id' => $this->pi->id]);

        Sanctum::actingAs($this->admin);
        $response = $this->getJson('/api/projects');

        $response->assertOk();
    }

    public function test_soft_delete_preserves_project(): void
    {
        $project = Project::factory()
            ->create([
                'pi_id' => $this->pi->id,
                'status_id' => ProjectStatus::where('name', 'draft')->first()->id,
            ]);

        Sanctum::actingAs($this->pi);
        $response = $this->deleteJson("/api/projects/{$project->id}");

        $response->assertOk();
        $this->assertSoftDeleted('projects', ['id' => $project->id]);
    }
}
