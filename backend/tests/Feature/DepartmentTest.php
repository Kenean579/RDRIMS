<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Permission;
use App\Models\Role;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    private University $universityA;
    private University $universityB;
    private Campus $campusA;
    private Campus $campusB;
    private Faculty $facultyA;
    private Faculty $facultyB;
    private Department $departmentA;
    private Department $departmentB;
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

        // Create departments
        $this->departmentA = Department::create([
            'name' => 'Department A',
            'code' => 'DEPT-A',
            'faculty_id' => $this->facultyA->id,
        ]);

        $this->departmentB = Department::create([
            'name' => 'Department B',
            'code' => 'DEPT-B',
            'faculty_id' => $this->facultyB->id,
        ]);

        // Create roles and permissions
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin'], ['description' => 'Platform Super Admin']);
        $researchAdminRole = Role::firstOrCreate(['name' => 'research_admin'], ['description' => 'University Research Admin']);

        // Create permissions
        $permissions = [
            'department.viewAny' => 'View departments',
            'department.view' => 'View individual department',
            'department.create' => 'Create departments',
            'department.update' => 'Update departments',
            'department.delete' => 'Delete departments',
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

    public function test_research_admin_can_view_departments_in_their_university(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->getJson('/api/departments');

        $response->assertOk();
        $response->assertJsonCount(1); // Only Department A
        $response->assertJsonFragment(['code' => 'DEPT-A']);
        $response->assertJsonMissing(['code' => 'DEPT-B']);
    }

    public function test_research_admin_cannot_view_departments_from_other_university(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->getJson('/api/departments');

        $response->assertOk();
        $response->assertJsonMissing(['code' => 'DEPT-B']);
    }

    public function test_super_admin_cannot_view_tenant_departments(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $response = $this->getJson('/api/departments');

        $response->assertForbidden();
    }

    public function test_research_admin_can_view_department_in_their_university(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->getJson("/api/departments/{$this->departmentA->id}");

        $response->assertOk();
        $response->assertJsonFragment(['code' => 'DEPT-A']);
    }

    public function test_research_admin_cannot_view_department_from_other_university(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->getJson("/api/departments/{$this->departmentB->id}");

        $response->assertForbidden();
    }

    public function test_super_admin_cannot_view_individual_department(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $response = $this->getJson("/api/departments/{$this->departmentA->id}");

        $response->assertForbidden();
    }

    public function test_research_admin_can_create_department_in_their_faculty(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->postJson('/api/departments', [
            'name' => 'New Department A',
            'code' => 'NEW-DEPT-A',
            'faculty_id' => $this->facultyA->id,
        ]);

        $response->assertCreated();
        $response->assertJsonFragment(['code' => 'NEW-DEPT-A']);
        $this->assertDatabaseHas('departments', ['code' => 'NEW-DEPT-A', 'faculty_id' => $this->facultyA->id]);
    }

    public function test_research_admin_cannot_create_department_in_other_university_faculty(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->postJson('/api/departments', [
            'name' => 'Malicious Department',
            'code' => 'MAL-DEPT',
            'faculty_id' => $this->facultyB->id, // Faculty from University B
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['faculty_id']);
        $this->assertDatabaseMissing('departments', ['code' => 'MAL-DEPT']);
    }

    public function test_super_admin_cannot_create_department(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $response = $this->postJson('/api/departments', [
            'name' => 'Super Admin Department',
            'code' => 'SA-DEPT',
            'faculty_id' => $this->facultyA->id,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('departments', ['code' => 'SA-DEPT']);
    }

    public function test_research_admin_can_update_department_in_their_university(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->putJson("/api/departments/{$this->departmentA->id}", [
            'name' => 'Updated Department A',
        ]);

        $response->assertOk();
        $response->assertJsonFragment(['name' => 'Updated Department A']);
        $this->assertDatabaseHas('departments', ['id' => $this->departmentA->id, 'name' => 'Updated Department A']);
    }

    public function test_research_admin_cannot_update_department_from_other_university(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->putJson("/api/departments/{$this->departmentB->id}", [
            'name' => 'Malicious Update',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('departments', ['id' => $this->departmentB->id, 'name' => 'Malicious Update']);
    }

    public function test_faculty_id_cannot_be_changed_on_update(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $originalFacultyId = $this->departmentA->faculty_id;

        $response = $this->putJson("/api/departments/{$this->departmentA->id}", [
            'faculty_id' => $this->facultyB->id, // Try to move to another faculty
            'name' => 'Updated Name',
        ]);

        // Should reject faculty_id change
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['faculty_id']);

        // Faculty should remain unchanged
        $this->assertDatabaseHas('departments', [
            'id' => $this->departmentA->id,
            'faculty_id' => $originalFacultyId,
        ]);
    }

    public function test_super_admin_cannot_update_department(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $response = $this->putJson("/api/departments/{$this->departmentA->id}", [
            'name' => 'Super Admin Update',
        ]);

        $response->assertForbidden();
    }

    public function test_research_admin_can_delete_department_in_their_university(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->deleteJson("/api/departments/{$this->departmentA->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('departments', ['id' => $this->departmentA->id]);
    }

    public function test_research_admin_cannot_delete_department_from_other_university(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->deleteJson("/api/departments/{$this->departmentB->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('departments', ['id' => $this->departmentB->id]);
    }

    public function test_super_admin_cannot_delete_department(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $response = $this->deleteJson("/api/departments/{$this->departmentA->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('departments', ['id' => $this->departmentA->id]);
    }

    public function test_unauthenticated_user_cannot_access_departments(): void
    {
        $response = $this->getJson('/api/departments');
        $response->assertUnauthorized();

        $response = $this->getJson("/api/departments/{$this->departmentA->id}");
        $response->assertUnauthorized();

        $response = $this->postJson('/api/departments', [
            'name' => 'Test',
            'code' => 'TEST',
            'faculty_id' => $this->facultyA->id,
        ]);
        $response->assertUnauthorized();
    }

    public function test_department_code_must_be_unique(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->postJson('/api/departments', [
            'name' => 'Duplicate Department',
            'code' => 'DEPT-A', // Already exists
            'faculty_id' => $this->facultyA->id,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['code']);
    }

    public function test_department_belongs_to_university_helper_works(): void
    {
        $this->assertTrue($this->departmentA->belongsToUniversity($this->universityA->id));
        $this->assertFalse($this->departmentA->belongsToUniversity($this->universityB->id));
    }

    public function test_department_belongs_to_faculty_helper_works(): void
    {
        $this->assertTrue($this->departmentA->belongsToFaculty($this->facultyA->id));
        $this->assertFalse($this->departmentA->belongsToFaculty($this->facultyB->id));
    }

    public function test_department_university_id_accessor_works(): void
    {
        $this->assertEquals($this->universityA->id, $this->departmentA->university_id);
        $this->assertNotEquals($this->universityB->id, $this->departmentA->university_id);
    }
}

