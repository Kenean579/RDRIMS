<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\Faculty;
use App\Models\Permission;
use App\Models\Role;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FacultyTest extends TestCase
{
    use RefreshDatabase;

    private University $universityA;
    private University $universityB;
    private Campus $campusA;
    private Campus $campusB;
    private Faculty $facultyA;
    private Faculty $facultyB;
    private User $researchAdminA;
    private User $researchAdminB;
    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create universities
        $this->universityA = University::create(['name' => 'University A', 'code' => 'UNI-A']);
        $this->universityB = University::create(['name' => 'University B', 'code' => 'UNI-B']);

        // Create campuses
        $this->campusA = Campus::create([
            'name' => 'Campus A',
            'code' => 'CAM-A',
            'university_id' => $this->universityA->id,
        ]);

        $this->campusB = Campus::create([
            'name' => 'Campus B',
            'code' => 'CAM-B',
            'university_id' => $this->universityB->id,
        ]);

        // Create faculties
        $this->facultyA = Faculty::create([
            'name' => 'Faculty A',
            'code' => 'FAC-A',
            'campus_id' => $this->campusA->id,
        ]);

        $this->facultyB = Faculty::create([
            'name' => 'Faculty B',
            'code' => 'FAC-B',
            'campus_id' => $this->campusB->id,
        ]);

        // Create roles and permissions
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin'], ['description' => 'Platform Super Admin']);
        $researchAdminRole = Role::firstOrCreate(['name' => 'research_admin'], ['description' => 'University Research Admin']);

        // Create permissions
        $permissions = [
            'faculty.viewAny' => 'View faculties',
            'faculty.view' => 'View individual faculty',
            'faculty.create' => 'Create faculties',
            'faculty.update' => 'Update faculties',
            'faculty.delete' => 'Delete faculties',
        ];

        foreach ($permissions as $name => $description) {
            $permission = Permission::create(['name' => $name, 'description' => $description]);
            $researchAdminRole->permissions()->attach($permission->id);
        }

        // Create users
        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'super@example.com',
            'password' => bcrypt('password'),
            'university_id' => null, // Platform-level admin
        ]);
        $this->superAdmin->roles()->attach($superAdminRole->id);

        $this->researchAdminA = User::create([
            'name' => 'Research Admin A',
            'email' => 'research.a@example.com',
            'password' => bcrypt('password'),
            'university_id' => $this->universityA->id,
        ]);
        $this->researchAdminA->roles()->attach($researchAdminRole->id);

        $this->researchAdminB = User::create([
            'name' => 'Research Admin B',
            'email' => 'research.b@example.com',
            'password' => bcrypt('password'),
            'university_id' => $this->universityB->id,
        ]);
        $this->researchAdminB->roles()->attach($researchAdminRole->id);
    }

    /** @test */
    public function research_admin_can_view_faculties_in_their_university(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->getJson('/api/faculties');

        $response->assertOk();
        $response->assertJsonCount(1); // Only Faculty A
        $response->assertJsonFragment(['code' => 'FAC-A']);
        $response->assertJsonMissing(['code' => 'FAC-B']);
    }

    /** @test */
    public function research_admin_cannot_view_faculties_from_other_university(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->getJson('/api/faculties');

        $response->assertOk();
        $response->assertJsonMissing(['code' => 'FAC-B']);
    }

    /** @test */
    public function super_admin_cannot_view_tenant_faculties(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $response = $this->getJson('/api/faculties');

        $response->assertForbidden();
    }

    /** @test */
    public function research_admin_can_view_faculty_in_their_university(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->getJson("/api/faculties/{$this->facultyA->id}");

        $response->assertOk();
        $response->assertJsonFragment(['code' => 'FAC-A']);
    }

    /** @test */
    public function research_admin_cannot_view_faculty_from_other_university(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->getJson("/api/faculties/{$this->facultyB->id}");

        $response->assertForbidden();
    }

    /** @test */
    public function super_admin_cannot_view_individual_faculty(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $response = $this->getJson("/api/faculties/{$this->facultyA->id}");

        $response->assertForbidden();
    }

    /** @test */
    public function research_admin_can_create_faculty_in_their_campus(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->postJson('/api/faculties', [
            'name' => 'New Faculty A',
            'code' => 'NEW-FAC-A',
            'campus_id' => $this->campusA->id,
        ]);

        $response->assertCreated();
        $response->assertJsonFragment(['code' => 'NEW-FAC-A']);
        $this->assertDatabaseHas('faculties', ['code' => 'NEW-FAC-A', 'campus_id' => $this->campusA->id]);
    }

    /** @test */
    public function research_admin_cannot_create_faculty_in_other_university_campus(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->postJson('/api/faculties', [
            'name' => 'Malicious Faculty',
            'code' => 'MAL-FAC',
            'campus_id' => $this->campusB->id, // Campus from University B
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['campus_id']);
        $this->assertDatabaseMissing('faculties', ['code' => 'MAL-FAC']);
    }

    /** @test */
    public function super_admin_cannot_create_faculty(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $response = $this->postJson('/api/faculties', [
            'name' => 'Super Admin Faculty',
            'code' => 'SA-FAC',
            'campus_id' => $this->campusA->id,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('faculties', ['code' => 'SA-FAC']);
    }

    /** @test */
    public function research_admin_can_update_faculty_in_their_university(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->putJson("/api/faculties/{$this->facultyA->id}", [
            'name' => 'Updated Faculty A',
        ]);

        $response->assertOk();
        $response->assertJsonFragment(['name' => 'Updated Faculty A']);
        $this->assertDatabaseHas('faculties', ['id' => $this->facultyA->id, 'name' => 'Updated Faculty A']);
    }

    /** @test */
    public function research_admin_cannot_update_faculty_from_other_university(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->putJson("/api/faculties/{$this->facultyB->id}", [
            'name' => 'Malicious Update',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('faculties', ['id' => $this->facultyB->id, 'name' => 'Malicious Update']);
    }

    /** @test */
    public function campus_id_cannot_be_changed_on_update(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $originalCampusId = $this->facultyA->campus_id;

        $response = $this->putJson("/api/faculties/{$this->facultyA->id}", [
            'campus_id' => $this->campusB->id, // Try to move to another campus
            'name' => 'Updated Name',
        ]);

        // Should reject campus_id change
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['campus_id']);

        // Campus should remain unchanged
        $this->assertDatabaseHas('faculties', [
            'id' => $this->facultyA->id,
            'campus_id' => $originalCampusId,
        ]);
    }

    /** @test */
    public function super_admin_cannot_update_faculty(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $response = $this->putJson("/api/faculties/{$this->facultyA->id}", [
            'name' => 'Super Admin Update',
        ]);

        $response->assertForbidden();
    }

    /** @test */
    public function research_admin_can_delete_faculty_in_their_university(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->deleteJson("/api/faculties/{$this->facultyA->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('faculties', ['id' => $this->facultyA->id]);
    }

    /** @test */
    public function research_admin_cannot_delete_faculty_from_other_university(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->deleteJson("/api/faculties/{$this->facultyB->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('faculties', ['id' => $this->facultyB->id]);
    }

    /** @test */
    public function super_admin_cannot_delete_faculty(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $response = $this->deleteJson("/api/faculties/{$this->facultyA->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('faculties', ['id' => $this->facultyA->id]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_faculties(): void
    {
        $response = $this->getJson('/api/faculties');
        $response->assertUnauthorized();

        $response = $this->getJson("/api/faculties/{$this->facultyA->id}");
        $response->assertUnauthorized();

        $response = $this->postJson('/api/faculties', [
            'name' => 'Test',
            'code' => 'TEST',
            'campus_id' => $this->campusA->id,
        ]);
        $response->assertUnauthorized();
    }
}
