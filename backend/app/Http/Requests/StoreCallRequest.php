<?php

namespace App\Http\Requests;

use App\Models\Campus;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\ResearchCenter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * StoreCallRequest
 * 
 * Validates call creation with:
 * - Tenant-aware validation (user owns specified institutions)
 * - Hierarchy consistency checks (campus→university, faculty→campus, etc.)
 * - IDOR prevention (cannot specify foreign institutions)
 */
class StoreCallRequest extends FormRequest
{
    /**
     * Determine whether the user can create a call.
     * Authorization is enforced by CallPolicy.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare data before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => trim((string) $this->title),
            'description' => trim((string) $this->description),
            'thematic_areas' => trim((string) $this->thematic_areas),
        ]);
    }

    /**
     * Validation rules for creating a call.
     */
    public function rules(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Basic Information
            |--------------------------------------------------------------------------
            */

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'required',
                'string',
            ],

            'thematic_areas' => [
                'required',
                'string',
            ],

            /*
            |--------------------------------------------------------------------------
            | Dates
            |--------------------------------------------------------------------------
            */

            'deadline' => [
                'required',
                'date',
                'after:today',
            ],

            'published_at' => [
                'nullable',
                'date',
            ],

            'opens_at' => [
                'nullable',
                'date',
            ],

            'closes_at' => [
                'nullable',
                'date',
                'after:opens_at',
            ],

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            'academic_year_id' => [
                'nullable',
                Rule::exists('academic_years', 'id'),
            ],

            'status_id' => [
                'nullable',
                Rule::exists('call_statuses', 'id'),
            ],

            'guideline_file_id' => [
                'nullable',
                Rule::exists('files', 'id'),
            ],

            /*
            |--------------------------------------------------------------------------
            | Institutional Hierarchy
            |--------------------------------------------------------------------------
            | 
            | IMPORTANT: university_id is REQUIRED (matches DB schema NOT NULL)
            | Other hierarchy fields are optional
            */

            'university_id' => [
                'required', // ← Changed from nullable (aligns with DB schema)
                Rule::exists('universities', 'id'),
            ],

            'research_center_id' => [
                'nullable',
                Rule::exists('research_centers', 'id'),
            ],

            'campus_id' => [
                'nullable',
                Rule::exists('campuses', 'id'),
            ],

            'faculty_id' => [
                'nullable',
                Rule::exists('faculties', 'id'),
            ],

            'department_id' => [
                'nullable',
                Rule::exists('departments', 'id'),
            ],

            /*
            |--------------------------------------------------------------------------
            | Options
            |--------------------------------------------------------------------------
            */

            'is_public' => [
                'sometimes',
                'boolean',
            ],

            'is_featured' => [
                'sometimes',
                'boolean',
            ],

            'metadata' => [
                'nullable',
                'array',
            ],
        ];
    }

    /**
     * Configure the validator instance with additional validation logic.
     * 
     * Performs:
     * 1. Tenant-aware validation (user owns specified university)
     * 2. Hierarchy consistency validation (campus→university, faculty→campus, etc.)
     * 3. IDOR prevention (cannot specify foreign institutions)
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $user = $this->user();
            
            if (!$user) {
                return; // Should not happen (auth middleware), but be defensive
            }

            $universityId = $this->input('university_id');
            $campusId = $this->input('campus_id');
            $facultyId = $this->input('faculty_id');
            $departmentId = $this->input('department_id');
            $researchCenterId = $this->input('research_center_id');

            /*
            |--------------------------------------------------------------------------
            | Tenant-Aware Validation: User owns university
            |--------------------------------------------------------------------------
            */

            if ($universityId && $user->university_id && $universityId != $user->university_id) {
                $validator->errors()->add(
                    'university_id',
                    'You can only create calls within your own university.'
                );
                return; // Stop validation if tenant check fails
            }

            /*
            |--------------------------------------------------------------------------
            | Hierarchy Validation: Campus → University
            |--------------------------------------------------------------------------
            */

            if ($campusId) {
                $campus = Campus::find($campusId);
                
                if (!$campus) {
                    $validator->errors()->add(
                        'campus_id',
                        'The selected campus does not exist.'
                    );
                    return;
                }

                if ($campus->university_id != $universityId) {
                    $validator->errors()->add(
                        'campus_id',
                        'The selected campus must belong to the selected university.'
                    );
                    return;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Hierarchy Validation: Faculty → Campus
            |--------------------------------------------------------------------------
            */

            if ($facultyId) {
                if (!$campusId) {
                    $validator->errors()->add(
                        'faculty_id',
                        'Faculty can only be selected when campus is specified.'
                    );
                    return;
                }

                $faculty = Faculty::find($facultyId);
                
                if (!$faculty) {
                    $validator->errors()->add(
                        'faculty_id',
                        'The selected faculty does not exist.'
                    );
                    return;
                }

                if ($faculty->campus_id != $campusId) {
                    $validator->errors()->add(
                        'faculty_id',
                        'The selected faculty must belong to the selected campus.'
                    );
                    return;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Hierarchy Validation: Department → Faculty
            |--------------------------------------------------------------------------
            */

            if ($departmentId) {
                if (!$facultyId) {
                    $validator->errors()->add(
                        'department_id',
                        'Department can only be selected when faculty is specified.'
                    );
                    return;
                }

                $department = Department::find($departmentId);
                
                if (!$department) {
                    $validator->errors()->add(
                        'department_id',
                        'The selected department does not exist.'
                    );
                    return;
                }

                if ($department->faculty_id != $facultyId) {
                    $validator->errors()->add(
                        'department_id',
                        'The selected department must belong to the selected faculty.'
                    );
                    return;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Hierarchy Validation: Research Center → University
            |--------------------------------------------------------------------------
            */

            if ($researchCenterId) {
                $center = ResearchCenter::find($researchCenterId);
                
                if (!$center) {
                    $validator->errors()->add(
                        'research_center_id',
                        'The selected research center does not exist.'
                    );
                    return;
                }

                if ($center->parent_university_id != $universityId) {
                    $validator->errors()->add(
                        'research_center_id',
                        'The selected research center must belong to the selected university.'
                    );
                }
            }
        });
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'university_id.required' => 'The university field is required.',
            'deadline.after' => 'The application deadline must be a future date.',
            'closes_at.after' => 'The closing date must be after the opening date.',
        ];
    }
}

