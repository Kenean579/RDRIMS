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
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    private $university1;
    private $university2;
    private $admin1;
    private $admin2;
    private $researcher1;
    private $fundingSource;
    private $budgetCategory;
    private $expenseCategory;

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

        // Create two universities
        $this->university1 = University::factory()->create(['name' => 'University 1']);
        $this->university2 = University::factory()->create(['name' => 'University 2']);

        // Create admins for each university
        $this->admin1 = User::factory()->create(['university_id' => $this->university1->id]);
        $this->admin1->assignRole('research_admin');

        $this->admin2 = User::factory()->create(['university_id' => $this->university2->id]);
        $this->admin2->assignRole('research_admin');

        // Create researcher in university 1
        $this->researcher1 = User::factory()->create(['university_id' => $this->university1->id]);

        // Create funding source for university 1
        $this->fundingSource = FundingSource::create([
            'university_id' => $this->university1->id,
            'name' => 'Gov Funding',
            'type' => 'government',
        ]);

        // Create categories for university 1
        $this->budgetCategory = BudgetCategory::create([
            'university_id' => $this->university1->id,
            'name' => 'Personnel',
        ]);

        $this->expenseCategory = ExpenseCategory::create([
            'university_id' => $this->university1->id,
            'name' => 'Salaries',
        ]);
    }

    /** @test */
    public function admin_cannot_access_other_universities_funding()
    {
        $funding = Funding::factory()
            ->for($this->university1)
            ->for($this->fundingSource)
            ->create(['created_by' => $this->admin1->id]);

        $response = $this->actingAs($this->admin2)->getJson("/api/fundings/{$funding->id}");

        $response->assertForbidden();
    }

    /** @test */
    public function user_cannot_create_funding_in_other_university()
    {
        $fundingSource2 = FundingSource::create([
            'university_id' => $this->university2->id,
            'name' => 'Gov Funding 2',
            'type' => 'government',
        ]);

        $response = $this->actingAs($this->admin1)->postJson('/api/fundings', [
            'funding_source_id' => $fundingSource2->id,
            'reference_number' => 'FUN-2026-001',
            'title' => 'Test',
            'total_amount' => 10000,
            'currency' => 'USD',
            'start_date' => now()->toIso8601String(),
            'end_date' => now()->addMonth()->toIso8601String(),
        ]);

        // Should fail because funding_source belongs to different university
        $response->assertUnprocessable();
    }

    /** @test */
    public function researcher_cannot_approve_funding_from_other_university()
    {
        $researcher2 = User::factory()->create(['university_id' => $this->university2->id]);

        $funding = Funding::factory()
            ->for($this->university1)
            ->for($this->fundingSource)
            ->create([
                'created_by' => $this->admin1->id,
                'status_id' => FundingStatus::where('name', 'submitted')->first()->id,
            ]);

        $response = $this->actingAs($researcher2)->postJson("/api/fundings/{$funding->id}/approve");

        $response->assertForbidden();
    }

    /** @test */
    public function user_cannot_update_approved_funding()
    {
        $funding = Funding::factory()
            ->for($this->university1)
            ->for($this->fundingSource)
            ->approved()
            ->create(['created_by' => $this->admin1->id]);

        $response = $this->actingAs($this->admin1)->putJson("/api/fundings/{$funding->id}", [
            'title' => 'New Title',
        ]);

        $response->assertForbidden();
    }

    /** @test */
    public function researcher_cannot_delete_submitted_funding()
    {
        $funding = Funding::factory()
            ->for($this->university1)
            ->for($this->fundingSource)
            ->create([
                'created_by' => $this->researcher1->id,
                'status_id' => FundingStatus::where('name', 'submitted')->first()->id,
            ]);

        $response = $this->actingAs($this->researcher1)->deleteJson("/api/fundings/{$funding->id}");

        $response->assertForbidden();
    }

    /** @test */
    public function unauthenticated_user_cannot_access_fundings()
    {
        $response = $this->getJson('/api/fundings');

        $response->assertUnauthorized();
    }

    /** @test */
    public function expense_cannot_be_approved_by_submitter()
    {
        $funding = Funding::factory()
            ->for($this->university1)
            ->for($this->fundingSource)
            ->approved()
            ->create();

        $expense = FundingExpense::factory()
            ->for($funding)
            ->for($this->budgetCategory)
            ->for($this->expenseCategory)
            ->pending()
            ->create(['submitted_by' => $this->researcher1->id]);

        $response = $this->actingAs($this->researcher1)->postJson(
            "/api/fundings/{$funding->id}/expenses/{$expense->id}/approve"
        );

        $response->assertForbidden();
    }

    /** @test */
    public function approved_expense_cannot_be_deleted()
    {
        $funding = Funding::factory()
            ->for($this->university1)
            ->for($this->fundingSource)
            ->approved()
            ->create();

        $expense = FundingExpense::factory()
            ->for($funding)
            ->for($this->budgetCategory)
            ->for($this->expenseCategory)
            ->create([
                'submitted_by' => $this->researcher1->id,
                'status' => 'approved',
                'approved_by' => $this->admin1->id,
                'approved_at' => now(),
            ]);

        $response = $this->actingAs($this->researcher1)->deleteJson(
            "/api/fundings/{$funding->id}/expenses/{$expense->id}"
        );

        $response->assertForbidden();
    }

    /** @test */
    public function funding_allocation_respects_tenant_isolation()
    {
        $funding = Funding::factory()
            ->for($this->university1)
            ->for($this->fundingSource)
            ->create();

        // Create category for different university
        $otherCategory = BudgetCategory::create([
            'university_id' => $this->university2->id,
            'name' => 'Other University Category',
        ]);

        // Try to allocate with category from different university
        // This should be handled by validation
        $response = $this->actingAs($this->admin1)->postJson("/api/fundings/{$funding->id}/allocations", [
            'budget_category_id' => $otherCategory->id,
            'allocated_amount' => 5000,
        ]);

        // Expect validation error or forbidden
        $this->assertTrue($response->status() >= 400);
    }

    /** @test */
    public function cannot_submit_expense_for_other_university_funding()
    {
        $funding = Funding::factory()
            ->for($this->university1)
            ->for($this->fundingSource)
            ->approved()
            ->create();

        $otherCategory = ExpenseCategory::create([
            'university_id' => $this->university2->id,
            'name' => 'Other Category',
        ]);

        $response = $this->actingAs($this->researcher1)->postJson(
            "/api/fundings/{$funding->id}/expenses",
            [
                'budget_category_id' => $this->budgetCategory->id,
                'expense_category_id' => $otherCategory->id,
                'reference_number' => 'EXP-001',
                'description' => 'Test',
                'amount' => 1000,
                'currency' => 'USD',
                'expense_date' => now()->toIso8601String(),
            ]
        );

        $this->assertTrue($response->status() >= 400);
    }

    /** @test */
    public function funding_history_logs_all_actions()
    {
        $funding = Funding::factory()
            ->for($this->university1)
            ->for($this->fundingSource)
            ->create(['created_by' => $this->admin1->id]);

        $histories = $funding->histories()->get();
        $this->assertGreaterThan(0, $histories->count());
        $this->assertTrue($histories->some(fn($h) => $h->action === 'created'));
    }

    /** @test */
    public function funding_approval_audit_trail_is_created()
    {
        $funding = Funding::factory()
            ->for($this->university1)
            ->for($this->fundingSource)
            ->create([
                'created_by' => $this->admin1->id,
                'status_id' => FundingStatus::where('name', 'submitted')->first()->id,
            ]);

        $this->actingAs($this->admin1)->postJson("/api/fundings/{$funding->id}/approve", [
            'comments' => 'Looks good',
        ]);

        $approval = $funding->approvals()->first();
        $this->assertNotNull($approval);
        $this->assertEquals('approved', $approval->action);
        $this->assertEquals($this->admin1->id, $approval->approved_by);
        $this->assertEquals('Looks good', $approval->comments);
    }

    /** @test */
    public function mass_assignment_protection_on_funding()
    {
        $funding = Funding::factory()
            ->for($this->university1)
            ->for($this->fundingSource)
            ->create(['created_by' => $this->admin1->id]);

        $response = $this->actingAs($this->admin1)->putJson("/api/fundings/{$funding->id}", [
            'status_id' => FundingStatus::where('name', 'approved')->first()->id,
            'approved_by' => $this->admin1->id,
        ]);

        // Status should not change via mass assignment
        $funding->refresh();
        $this->assertNotEquals('approved', $funding->status->name);
    }
}
