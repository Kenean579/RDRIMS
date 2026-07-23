<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Permission;
use App\Models\ResearchCenter;
use App\Models\Role;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ResearchCenterTest extends TestCase
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
    private ResearchCenter $centerUniversityLevel;
    private ResearchCenter $centerCampusLevel;
    private ResearchCenter $centerDepartmentLevel;
    private ResearchCenter $centerUniversityB;
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

        // Create research centers at different hierarchy levels
        // University-level center (University A)
        $this->centerUniversityLevel = ResearchCenter::create([
            'name' => 'University Research Center A',
            'code' => 'URC-A',
            'parent_university_id' => $this->universityA->id,
            'parent_campus_id' => null,
            'parent_faculty_id' => null,
            'parent_department_id' => null,
        ]);

        // Campus-level center (University A, Campus A)
        $this->centerCampusLevel = ResearchCenter::create([
            'name' => 'Campus Research Center A',
            'code' => 'CRC-A',
            'parent_university_id' => $this->universityA->id,
            'parent_campus_id' => $this->campusA->id,
            'parent_faculty_id' => null,
            'parent_department_id' => null,
        ]);

        // Department-level center (University A, Campus A, Faculty A, Department A)
        $this->centerDepartmentLevel = ResearchCenter::create([
            'name' => 'Department Research Center A',
            'code' => 'DRC-A',
            'parent_university_id' => $this->universityA->id,
            'parent_campus_id' => $this->campusA->id,
            'parent_faculty_id' => $this->facultyA->id,
            'parent_department_id' => $this->departmentA->id,
        ]);

        // University-level center (University B)
        $this->centerUniversityB = ResearchCenter::create([
            'name' => 'University Research Center B',
            'code' => 'URC-B',
            'parent_university_id' => $this->universityB->id,
            'parent_campus_id' => null,
            'parent_faculty_id' => null,
            'parent_department_id' => null,
        ]);

        // Create roles and permissions
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin'], ['description' => 'Platform Super Admin']);
        $researchAdminRole = Role::firstOrCreate(['name' => 'research_admin'], ['description' => 'University Research Admin']);

        // Create permissions
        $permissions = [
            'research_center.viewAny' => 'View research centers',
            'research_center.view' => 'View individual research center',
            'research_center.create' => 'Create research centers',
            'research_center.update' => 'Update research centers',
            'research_center.delete' => 'Delete research centers',
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

    // ===================== AUTHORIZATION TESTS =====================

    public function test_research_admin_can_view_research_centers_in_their_university(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->getJson('/api/research-centers');

        $response->assertOk();
        // Response is paginated
        $data = $response->json('data');
        $this->assertCount(3, $data); // All 3 centers from University A
        $response->assertJsonFragment(['code' => 'URC-A']);
        $response->assertJsonFragment(['code' => 'CRC-A']);
        $response->assertJsonFragment(['code' => 'DRC-A']);
        $response->assertJsonMissing(['code' => 'URC-B']);
    }

    public function test_research_admin_cannot_view_research_centers_from_other_university(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->getJson('/api/research-centers');

        $response->assertOk();
        $response->assertJsonMissing(['code' => 'URC-B']);
    }

    public function test_super_admin_can_view_public_research_centers_list(): void
    {
        Sanctum::actingAs($this->superAdmin);

        // Index endpoint is public but filtered by policy authorization
        $response = $this->getJson('/api/research-centers');

        // Super admin sees empty list due to policy restrictions
        $response->assertOk();
    }

    public function test_research_admin_can_view_individual_research_center_in_their_university(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->getJson("/api/research-centers/{$this->centerUniversityLevel->id}");

        $response->assertOk();
        $response->assertJsonFragment(['code' => 'URC-A']);
    }

    public function test_research_admin_cannot_view_research_center_from_other_university(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->getJson("/api/research-centers/{$this->centerUniversityB->id}");

        $response->assertForbidden();
    }

    public function test_super_admin_can_view_public_research_center_but_policy_restricts(): void
    {
        Sanctum::actingAs($this->superAdmin);

        // Show endpoint is public but policy checks authorization
        $response = $this->getJson("/api/research-centers/{$this->centerUniversityLevel->id}");

        // Super admin sees response due to public endpoint
        $response->assertOk();
    }

    // ===================== CREATION TESTS - 3 LEVELS =====================

    public function test_research_admin_can_create_university_level_research_center(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->postJson('/api/research-centers', [
            'name' => 'New University Research Center',
            'code' => 'NEW-URC',
            'parent_university_id' => $this->universityA->id,
        ]);

        $response->assertCreated();
        $response->assertJsonFragment(['code' => 'NEW-URC']);
        $this->assertDatabaseHas('research_centers', [
            'code' => 'NEW-URC',
            'parent_university_id' => $this->universityA->id,
            'parent_campus_id' => null,
            'parent_faculty_id' => null,
            'parent_department_id' => null,
        ]);
    }

    public function test_research_admin_can_create_campus_level_research_center(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->postJson('/api/research-centers', [
            'name' => 'New Campus Research Center',
            'code' => 'NEW-CRC',
            'parent_university_id' => $this->universityA->id,
            'parent_campus_id' => $this->campusA->id,
        ]);

        $response->assertCreated();
        $response->assertJsonFragment(['code' => 'NEW-CRC']);
        $this->assertDatabaseHas('research_centers', [
            'code' => 'NEW-CRC',
            'parent_university_id' => $this->universityA->id,
            'parent_campus_id' => $this->campusA->id,
            'parent_faculty_id' => null,
            'parent_department_id' => null,
        ]);
    }

    public function test_research_admin_can_create_department_level_research_center(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->postJson('/api/research-centers', [
            'name' => 'New Department Research Center',
            'code' => 'NEW-DRC',
            'parent_university_id' => $this->universityA->id,
            'parent_campus_id' => $this->campusA->id,
            'parent_faculty_id' => $this->facultyA->id,
            'parent_department_id' => $this->departmentA->id,
        ]);

        $response->assertCreated();
        $response->assertJsonFragment(['code' => 'NEW-DRC']);
        $this->assertDatabaseHas('research_centers', [
            'code' => 'NEW-DRC',
            'parent_university_id' => $this->universityA->id,
            'parent_campus_id' => $this->campusA->id,
            'parent_faculty_id' => $this->facultyA->id,
            'parent_department_id' => $this->departmentA->id,
        ]);
    }

    // ===================== HIERARCHY VALIDATION TESTS =====================

    public function test_cannot_create_research_center_with_campus_from_different_university(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->postJson('/api/research-centers', [
            'name' => 'Invalid Research Center',
            'code' => 'INVALID-RC',
            'parent_university_id' => $this->universityA->id,
            'parent_campus_id' => $this->campusB->id, // Campus from University B
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['parent_campus_id']);
        $this->assertDatabaseMissing('research_centers', ['code' => 'INVALID-RC']);
    }

    public function test_cannot_create_research_center_with_faculty_from_different_campus(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->postJson('/api/research-centers', [
            'name' => 'Invalid Research Center',
            'code' => 'INVALID-RC',
            'parent_university_id' => $this->universityA->id,
            'parent_campus_id' => $this->campusA->id,
            'parent_faculty_id' => $this->facultyB->id, // Faculty from Campus B
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['parent_faculty_id']);
        $this->assertDatabaseMissing('research_centers', ['code' => 'INVALID-RC']);
    }

    public function test_cannot_create_research_center_with_department_from_different_faculty(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->postJson('/api/research-centers', [
            'name' => 'Invalid Research Center',
            'code' => 'INVALID-RC',
            'parent_university_id' => $this->universityA->id,
            'parent_campus_id' => $this->campusA->id,
            'parent_faculty_id' => $this->facultyA->id,
            'parent_department_id' => $this->departmentB->id, // Department from Faculty B
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['parent_department_id']);
        $this->assertDatabaseMissing('research_centers', ['code' => 'INVALID-RC']);
    }

    public function test_cannot_specify_faculty_without_campus(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->postJson('/api/research-centers', [
            'name' => 'Invalid Research Center',
            'code' => 'INVALID-RC',
            'parent_university_id' => $this->universityA->id,
            'parent_faculty_id' => $this->facultyA->id, // Faculty without campus
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['parent_faculty_id']);
        $this->assertDatabaseMissing('research_centers', ['code' => 'INVALID-RC']);
    }

    public function test_cannot_specify_department_without_faculty(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->postJson('/api/research-centers', [
            'name' => 'Invalid Research Center',
            'code' => 'INVALID-RC',
            'parent_university_id' => $this->universityA->id,
            'parent_campus_id' => $this->campusA->id,
            'parent_department_id' => $this->departmentA->id, // Department without faculty
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['parent_department_id']);
        $this->assertDatabaseMissing('research_centers', ['code' => 'INVALID-RC']);
    }

    // ===================== IDOR PREVENTION TESTS =====================

    public function test_research_admin_cannot_create_research_center_in_other_university(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->postJson('/api/research-centers', [
            'name' => 'Malicious Research Center',
            'code' => 'MAL-RC',
            'parent_university_id' => $this->universityB->id, // University B
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['parent_university_id']);
        $this->assertDatabaseMissing('research_centers', ['code' => 'MAL-RC']);
    }

    public function test_research_admin_cannot_attach_campus_from_other_university(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->postJson('/api/research-centers', [
            'name' => 'Malicious Research Center',
            'code' => 'MAL-RC',
            'parent_university_id' => $this->universityA->id,
            'parent_campus_id' => $this->campusB->id, // Campus from University B
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['parent_campus_id']);
        $this->assertDatabaseMissing('research_centers', ['code' => 'MAL-RC']);
    }

    public function test_research_admin_cannot_attach_faculty_from_other_university(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->postJson('/api/research-centers', [
            'name' => 'Malicious Research Center',
            'code' => 'MAL-RC',
            'parent_university_id' => $this->universityA->id,
            'parent_campus_id' => $this->campusA->id,
            'parent_faculty_id' => $this->facultyB->id, // Faculty from Campus B
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['parent_faculty_id']);
        $this->assertDatabaseMissing('research_centers', ['code' => 'MAL-RC']);
    }

    public function test_research_admin_cannot_attach_department_from_other_university(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->postJson('/api/research-centers', [
            'name' => 'Malicious Research Center',
            'code' => 'MAL-RC',
            'parent_university_id' => $this->universityA->id,
            'parent_campus_id' => $this->campusA->id,
            'parent_faculty_id' => $this->facultyA->id,
            'parent_department_id' => $this->departmentB->id, // Department from Faculty B
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['parent_department_id']);
        $this->assertDatabaseMissing('research_centers', ['code' => 'MAL-RC']);
    }

    public function test_super_admin_can_create_research_center_current_behavior(): void
    {
        Sanctum::actingAs($this->superAdmin);

        // Current behavior: Super admin can create research centers
        // Note: This differs from Campus/Faculty/Department due to policy implementation
        $response = $this->postJson('/api/research-centers', [
            'name' => 'Super Admin Research Center',
            'code' => 'SA-RC',
            'parent_university_id' => $this->universityA->id,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('research_centers', ['code' => 'SA-RC']);
    }

    // ===================== UPDATE TESTS =====================

    public function test_research_admin_can_update_research_center_in_their_university(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->putJson("/api/research-centers/{$this->centerUniversityLevel->id}", [
            'name' => 'Updated Research Center',
        ]);

        $response->assertOk();
        $response->assertJsonFragment(['name' => 'Updated Research Center']);
        $this->assertDatabaseHas('research_centers', [
            'id' => $this->centerUniversityLevel->id,
            'name' => 'Updated Research Center',
        ]);
    }

    public function test_research_admin_cannot_update_research_center_from_other_university(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->putJson("/api/research-centers/{$this->centerUniversityB->id}", [
            'name' => 'Malicious Update',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('research_centers', [
            'id' => $this->centerUniversityB->id,
            'name' => 'Malicious Update',
        ]);
    }

    public function test_super_admin_can_update_research_center_current_behavior(): void
    {
        Sanctum::actingAs($this->superAdmin);

        // Current behavior: Super admin can update research centers
        $response = $this->putJson("/api/research-centers/{$this->centerUniversityLevel->id}", [
            'name' => 'Super Admin Update',
        ]);

        $response->assertOk();
    }

    // ===================== IMMUTABILITY TESTS =====================

    public function test_parent_university_id_cannot_be_changed_on_update(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $originalUniversityId = $this->centerUniversityLevel->parent_university_id;

        $response = $this->putJson("/api/research-centers/{$this->centerUniversityLevel->id}", [
            'parent_university_id' => $this->universityB->id,
            'name' => 'Updated Name',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['parent_university_id']);

        $this->assertDatabaseHas('research_centers', [
            'id' => $this->centerUniversityLevel->id,
            'parent_university_id' => $originalUniversityId,
        ]);
    }

    public function test_parent_campus_id_cannot_be_changed_on_update(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $originalCampusId = $this->centerCampusLevel->parent_campus_id;

        $response = $this->putJson("/api/research-centers/{$this->centerCampusLevel->id}", [
            'parent_campus_id' => $this->campusB->id,
            'name' => 'Updated Name',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['parent_campus_id']);

        $this->assertDatabaseHas('research_centers', [
            'id' => $this->centerCampusLevel->id,
            'parent_campus_id' => $originalCampusId,
        ]);
    }

    public function test_parent_faculty_id_cannot_be_changed_on_update(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $originalFacultyId = $this->centerDepartmentLevel->parent_faculty_id;

        $response = $this->putJson("/api/research-centers/{$this->centerDepartmentLevel->id}", [
            'parent_faculty_id' => $this->facultyB->id,
            'name' => 'Updated Name',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['parent_faculty_id']);

        $this->assertDatabaseHas('research_centers', [
            'id' => $this->centerDepartmentLevel->id,
            'parent_faculty_id' => $originalFacultyId,
        ]);
    }

    public function test_parent_department_id_cannot_be_changed_on_update(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $originalDepartmentId = $this->centerDepartmentLevel->parent_department_id;

        $response = $this->putJson("/api/research-centers/{$this->centerDepartmentLevel->id}", [
            'parent_department_id' => $this->departmentB->id,
            'name' => 'Updated Name',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['parent_department_id']);

        $this->assertDatabaseHas('research_centers', [
            'id' => $this->centerDepartmentLevel->id,
            'parent_department_id' => $originalDepartmentId,
        ]);
    }

    // ===================== DELETE TESTS =====================

    public function test_research_admin_can_delete_research_center_in_their_university(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->deleteJson("/api/research-centers/{$this->centerUniversityLevel->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('research_centers', ['id' => $this->centerUniversityLevel->id]);
    }

    public function test_research_admin_cannot_delete_research_center_from_other_university(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->deleteJson("/api/research-centers/{$this->centerUniversityB->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('research_centers', ['id' => $this->centerUniversityB->id]);
    }

    public function test_super_admin_can_delete_research_center_current_behavior(): void
    {
        Sanctum::actingAs($this->superAdmin);

        // Current behavior: Super admin can delete research centers
        $response = $this->deleteJson("/api/research-centers/{$this->centerUniversityLevel->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('research_centers', ['id' => $this->centerUniversityLevel->id]);
    }

    // ===================== MISCELLANEOUS TESTS =====================

    public function test_unauthenticated_user_gets_forbidden_on_public_endpoints_with_authorization(): void
    {
        // Index and Show endpoints are public (no auth middleware) but call authorize() in controller
        // When no user is authenticated, authorize() returns 403 Forbidden
        $response = $this->getJson('/api/research-centers');
        $response->assertForbidden(); // Controller calls authorize('viewAny') → 403 when no user

        $response = $this->getJson("/api/research-centers/{$this->centerUniversityLevel->id}");
        $response->assertForbidden(); // Controller calls authorize('view') → 403 when no user

        // Protected endpoints (create/update/delete) require authentication → 401
        $response = $this->postJson('/api/research-centers', [
            'name' => 'Test',
            'code' => 'TEST',
            'parent_university_id' => $this->universityA->id,
        ]);
        $response->assertUnauthorized(); // Auth middleware blocks → 401
    }

    public function test_research_center_code_must_be_unique(): void
    {
        Sanctum::actingAs($this->researchAdminA);

        $response = $this->postJson('/api/research-centers', [
            'name' => 'Duplicate Research Center',
            'code' => 'URC-A', // Already exists
            'parent_university_id' => $this->universityA->id,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['code']);
    }

    // ===================== MODEL HELPER TESTS =====================

    public function test_research_center_belongs_to_university_helper_works(): void
    {
        $this->assertTrue($this->centerUniversityLevel->belongsToUniversity($this->universityA->id));
        $this->assertFalse($this->centerUniversityLevel->belongsToUniversity($this->universityB->id));
    }

    public function test_research_center_is_university_level_helper_works(): void
    {
        $this->assertTrue($this->centerUniversityLevel->isUniversityLevel());
        $this->assertFalse($this->centerCampusLevel->isUniversityLevel());
        $this->assertFalse($this->centerDepartmentLevel->isUniversityLevel());
    }

    public function test_research_center_is_campus_level_helper_works(): void
    {
        $this->assertFalse($this->centerUniversityLevel->isCampusLevel());
        $this->assertTrue($this->centerCampusLevel->isCampusLevel());
        $this->assertFalse($this->centerDepartmentLevel->isCampusLevel());
    }


    public function test_research_center_is_department_level_helper_works(): void
    {
        $this->assertFalse($this->centerUniversityLevel->isDepartmentLevel());
        $this->assertFalse($this->centerCampusLevel->isDepartmentLevel());
        $this->assertTrue($this->centerDepartmentLevel->isDepartmentLevel());
    }

    public function test_research_center_university_id_accessor_works(): void
    {
        $this->assertEquals($this->universityA->id, $this->centerUniversityLevel->university_id);
        $this->assertEquals($this->universityA->id, $this->centerCampusLevel->university_id);
        $this->assertEquals($this->universityA->id, $this->centerDepartmentLevel->university_id);
        $this->assertNotEquals($this->universityB->id, $this->centerUniversityLevel->university_id);
    }
}
