<?php

namespace Tests\Feature\Funding;

use App\Models\Funding;
use App\Models\FundingExpense;
use App\Models\FundingSource;
use App\Models\FundingStatus;
use App\Models\BudgetCategory;
use App\Models\ExpenseCategory;
use App\Models\User;
use App\Models\University;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FundingModuleTest extends TestCase
{
    use RefreshDatabase;

    private $university;
    private $admin;
    private $researcher;
    private $fundingSource;
    private $budgetCategory;
    private $expenseCategory;
    private $draftStatus;
    private $submittedStatus;
    private $approvedStatus;
    private $rejectedStatus;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear and seed funding statuses
        DB::table('funding_statuses')->truncate();
        DB::table('funding_statuses')->insert([
            ['id' => 1, 'name' => 'draft', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'submitted', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'under_review', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'approved', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'name' => 'rejected', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'name' => 'closed', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Create university
        $this->university = University::factory()->create();

        // Create users
        $this->admin = User::factory()->create(['university_id' => $this->university->id]);
        $this->admin->assignRole('research_admin');

        $this->researcher = User::factory()->create(['university_id' => $this->university->id]);

        // Store status references
        $this->draftStatus = FundingStatus::find(1);
        $this->submittedStatus = FundingStatus::find(2);
        $this->approvedStatus = FundingStatus::find(4);
        $this->rejectedStatus = FundingStatus::find(5);

        // Create funding source
        $this->fundingSource = FundingSource::create([
            'university_id' => $this->university->id,
            'name' => 'Government Funding',
            'type' => 'government',
            'is_active' => true,
        ]);

        // Create categories
        $this->budgetCategory = BudgetCategory::create([
            'university_id' => $this->university->id,
            'name' => 'Personnel',
            'is_active' => true,
        ]);

        $this->expenseCategory = ExpenseCategory::create([
            'university_id' => $this->university->id,
            'name' => 'Salaries',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_create_funding(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/fundings', [
            'funding_source_id' => $this->fundingSource->id,
            'reference_number' => 'FUN-2026-001',
            'title' => 'Research Project Funding',
            'description' => 'Funding for AI research',
            'total_amount' => 50000,
            'currency' => 'USD',
            'start_date' => now()->toIso8601String(),
            'end_date' => now()->addYear()->toIso8601String(),
            'is_internal' => false,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('fundings', [
            'reference_number' => 'FUN-2026-001',
            'title' => 'Research Project Funding',
        ]);
    }

    public function test_funding_starts_in_draft_status(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/fundings', [
            'funding_source_id' => $this->fundingSource->id,
            'reference_number' => 'FUN-2026-002',
            'title' => 'Test Funding',
            'total_amount' => 10000,
            'currency' => 'USD',
            'start_date' => now()->toIso8601String(),
            'end_date' => now()->addMonth()->toIso8601String(),
        ]);

        $response->assertCreated();
        
        // Check via database using stored status
        $this->assertDatabaseHas('fundings', [
            'reference_number' => 'FUN-2026-002',
            'status_id' => $this->draftStatus->id,
        ]);
    }

    public function test_creator_can_submit_funding(): void
    {
        $funding = Funding::factory()
            ->for($this->university)
            ->for($this->fundingSource)
            ->create([
                'created_by' => $this->admin->id,
            ]);

        Sanctum::actingAs($this->admin);
        $response = $this->postJson("/api/fundings/{$funding->id}/submit");

        $response->assertOk();
        
        // Check via database using stored status
        $this->assertDatabaseHas('fundings', [
            'id' => $funding->id,
            'status_id' => $this->submittedStatus->id,
        ]);
    }

    public function test_admin_can_approve_funding(): void
    {
        $funding = Funding::factory()
            ->for($this->university)
            ->for($this->fundingSource)
            ->create([
                'created_by' => $this->researcher->id,
                'status_id' => $this->submittedStatus->id,
            ]);

        Sanctum::actingAs($this->admin);
        $response = $this->postJson("/api/fundings/{$funding->id}/approve", [
            'comments' => 'Approved for budget',
        ]);

        $response->assertOk();
        
        // Check via database using stored status
        $this->assertDatabaseHas('fundings', [
            'id' => $funding->id,
            'status_id' => $this->approvedStatus->id,
            'approved_by' => $this->admin->id,
        ]);
    }

    public function test_admin_can_reject_funding(): void
    {
        $funding = Funding::factory()
            ->for($this->university)
            ->for($this->fundingSource)
            ->create([
                'created_by' => $this->researcher->id,
                'status_id' => $this->submittedStatus->id,
            ]);

        Sanctum::actingAs($this->admin);
        $response = $this->postJson("/api/fundings/{$funding->id}/reject", [
            'comments' => 'Insufficient budget',
        ]);

        $response->assertOk();
        
        // Check via database using stored status
        $this->assertDatabaseHas('fundings', [
            'id' => $funding->id,
            'status_id' => $this->rejectedStatus->id,
        ]);
    }

    public function test_expense_can_be_recorded(): void
    {
        $funding = Funding::factory()
            ->for($this->university)
            ->for($this->fundingSource)
            ->approved()
            ->create([
                'created_by' => $this->researcher->id, // Changed from admin to researcher
            ]);

        Sanctum::actingAs($this->researcher);
        $response = $this->postJson("/api/fundings/{$funding->id}/expenses", [
            'budget_category_id' => $this->budgetCategory->id,
            'expense_category_id' => $this->expenseCategory->id,
            'reference_number' => 'EXP-001',
            'description' => 'Monthly salary payment',
            'amount' => 5000,
            'currency' => 'USD',
            'expense_date' => now()->toIso8601String(),
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('funding_expenses', [
            'reference_number' => 'EXP-001',
            'status' => 'pending',
        ]);
    }

    public function test_admin_can_approve_expense(): void
    {
        $funding = Funding::factory()
            ->for($this->university)
            ->for($this->fundingSource)
            ->approved()
            ->create();

        $expense = FundingExpense::factory()
            ->for($funding)
            ->for($this->budgetCategory)
            ->for($this->expenseCategory)
            ->pending()
            ->create([
                'submitted_by' => $this->researcher->id,
            ]);

        Sanctum::actingAs($this->admin);
        $response = $this->postJson(
            "/api/fundings/{$funding->id}/expenses/{$expense->id}/approve",
            ['comments' => 'Verified and approved']
        );

        $response->assertOk();
        $this->assertEquals('approved', $expense->fresh()->status);
        $this->assertEquals($this->admin->id, $expense->fresh()->approved_by);
    }

    public function test_can_list_fundings_with_pagination(): void
    {
        Funding::factory(5)
            ->for($this->university)
            ->for($this->fundingSource)
            ->create();

        Sanctum::actingAs($this->admin);
        $response = $this->getJson('/api/fundings');

        $response->assertOk();
        $this->assertCount(5, $response->json('data'));
    }

    public function test_can_get_budget_statistics(): void
    {
        $funding = Funding::factory()
            ->for($this->university)
            ->for($this->fundingSource)
            ->create(['total_amount' => 50000]);

        \App\Models\FundingAllocation::create([
            'funding_id' => $funding->id,
            'budget_category_id' => $this->budgetCategory->id,
            'allocated_amount' => 30000,
            'used_amount' => 5000,
        ]);

        Sanctum::actingAs($this->admin);
        $response = $this->getJson("/api/fundings/{$funding->id}/budget-stats");

        $response->assertOk();
        $this->assertEquals(30000, $response->json('total_allocated'));
        $this->assertEquals(5000, $response->json('total_used'));
    }
}
