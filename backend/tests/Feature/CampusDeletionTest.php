<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\University;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampusDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_campus_with_faculties_and_departments_can_be_deleted(): void
    {
        $this->withoutMiddleware();

        $university = University::create([
            'name' => 'Test University',
            'code' => 'TU',
            'location' => 'Addis Ababa',
        ]);

        $campus = Campus::create([
            'name' => 'North Campus',
            'code' => 'NC',
            'university_id' => $university->id,
        ]);

        $faculty = Faculty::create([
            'name' => 'Engineering Faculty',
            'code' => 'ENG',
            'campus_id' => $campus->id,
        ]);

        $department = Department::create([
            'name' => 'Computer Science',
            'code' => 'CS',
            'faculty_id' => $faculty->id,
        ]);

        $response = $this->deleteJson("/api/campuses/{$campus->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('campuses', ['id' => $campus->id]);
        $this->assertDatabaseMissing('faculties', ['id' => $faculty->id]);
        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
    }
}
