<?php

namespace App\Http\Requests;

use App\Models\Campus;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\ResearchCenter;
use App\Services\CallService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * UpdateCallRequest
 * 
 * Validates call updates with:
 * - Immutability protection (university_id cannot change)
 * - Status-based edit restrictions (workflow-critical fields locked when open/closed)
 * - Hierarchy consistency validation (fields remain aligned with university)
 * - Status transition validation (enforces draft→open→closed)
 */
class UpdateCallRequest extends FormRequest
{
    /**
     * Determine whether the user is authorized.
     * Authorization is handled by CallPolicy.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Clean incoming data before validation.
     */
    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('title')) {
            $data['title'] = trim((string) $this->title);
        }

        if ($this->has('description')) {
            $data['description'] = trim((string) $this->description);
        }

        if ($this->has('thematic_areas')) {
            $data['thematic_areas'] = trim((string) $this->thematic_areas);
        }

        $this->merge($data);
    }

    /**
     * Validation rules.
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
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'sometimes',
                'required',
                'string',
            ],

            'thematic_areas' => [
                'sometimes',
                'required',
                'string',
            ],

            /*
            |--------------------------------------------------------------------------
            | Dates
            |--------------------------------------------------------------------------
            */

            'deadline' => [
                'sometimes',
                'required',
                'date',
            ],

            'published_at' => [
                'sometimes',
                'nullable',
                'date',
            ],

            'opens_at' => [
                'sometimes',
                'nullable',
                'date',
            ],

            'closes_at' => [
                'sometimes',
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
                'sometimes',
                'nullable',
                Rule::exists('academic_years', 'id'),
            ],

            'status_id' => [
                'sometimes',
                'nullable',
                Rule::exists('call_statuses', 'id'),
            ],

            'guideline_file_id' => [
                'sometimes',
                'nullable',
                Rule::exists('files', 'id'),
            ],

            /*
            |--------------------------------------------------------------------------
            | Institution Hierarchy
            |--------------------------------------------------------------------------
            | 
            | IMPORTANT: university_id is IMMUTABLE (enforced in withValidator)
            | Other hierarchy fields can be updated but must remain consistent
            */

            'university_id' => [
                'sometimes',
                'nullable',
                Rule::exists('universities', 'id'),
            ],

            'research_center_id' => [
                'sometimes',
                'nullable',
                Rule::exists('research_centers', 'id'),
            ],

            'campus_id' => [
                'sometimes',
                'nullable',
                Rule::exists('campuses', 'id'),
            ],

            'faculty_id' => [
                'sometimes',
                'nullable',
                Rule::exists('faculties', 'id'),
            ],

            'department_id' => [
                'sometimes',
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
                'sometimes',
                'nullable',
                'array',
            ],
        ];
    }

    /**
     * Configure the validator instance with additional validation logic.
     * 
     * Enforces:
     * 1. Immutability: university_id cannot be changed
     * 2. Status-based restrictions: workflow-critical fields locked when open/closed
     * 3. Status transitions: validate draft→open→closed
     * 4. Hierarchy consistency: validate fields belong to call's university
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $call = $this->route('call');
            
            if (!$call) {
                return; // Should not happen, but be defensive
            }

            /*
            |--------------------------------------------------------------------------
            | Immutability Protection: university_id cannot change
            |--------------------------------------------------------------------------
            */

            if ($this->has('university_id')) {
                $validator->errors()->add(
                    'university_id',
                    'The university cannot be changed after creation.'
                );
                return; // Stop validation if immutability violated
            }

            /*
            |--------------------------------------------------------------------------
            | Status-Based Edit Restrictions
            |--------------------------------------------------------------------------
            | 
            | Use CallService to check if fields can be edited based on call status
            */

            $callService = app(CallService::class);
            $fieldsToUpdate = $this->only(array_keys($this->rules()));
            
            $editCheck = $callService->canEdit($call, $fieldsToUpdate);
            
            if (!$editCheck['allowed']) {
                $restrictedFields = implode(', ', $editCheck['restricted_fields']);
                $validator->errors()->add(
                    'status',
                    "Cannot edit the following fields when call is {$call->status?->name}: {$restrictedFields}. These fields are locked to protect data integrity."
                );
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Status Transition Validation
            |--------------------------------------------------------------------------
            */

            if ($this->has('status_id')) {
                $newStatusId = $this->input('status_id');
                
                if (!$callService->validateStatusTransition($call, $newStatusId)) {
                    $currentStatus = $call->status?->name ?? 'unknown';
                    $validator->errors()->add(
                        'status_id',
                        "Invalid status transition from '{$currentStatus}'. Valid transitions: draft→open→closed (no reopening)."
                    );
                    return;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Hierarchy Consistency Validation
            |--------------------------------------------------------------------------
            | 
            | Ensure updated hierarchy fields remain consistent with call's university
            */

            $campusId = $this->input('campus_id');
            $facultyId = $this->input('faculty_id');
            $departmentId = $this->input('department_id');
            $researchCenterId = $this->input('research_center_id');

            // Validate campus belongs to call's university
            if ($this->has('campus_id') && $campusId) {
                $campus = Campus::find($campusId);
                
                if (!$campus) {
                    $validator->errors()->add(
                        'campus_id',
                        'The selected campus does not exist.'
                    );
                    return;
                }

                if ($campus->university_id != $call->university_id) {
                    $validator->errors()->add(
                        'campus_id',
                        'The selected campus must belong to the call\'s university.'
                    );
                    return;
                }
            }

            // Validate faculty belongs to campus
            if ($this->has('faculty_id') && $facultyId) {
                $faculty = Faculty::find($facultyId);
                
                if (!$faculty) {
                    $validator->errors()->add(
                        'faculty_id',
                        'The selected faculty does not exist.'
                    );
                    return;
                }

                $effectiveCampusId = $this->has('campus_id') ? $campusId : $call->campus_id;
                
                if (!$effectiveCampusId) {
                    $validator->errors()->add(
                        'faculty_id',
                        'Faculty can only be selected when campus is specified.'
                    );
                    return;
                }

                if ($faculty->campus_id != $effectiveCampusId) {
                    $validator->errors()->add(
                        'faculty_id',
                        'The selected faculty must belong to the call\'s campus.'
                    );
                    return;
                }
            }

            // Validate department belongs to faculty
            if ($this->has('department_id') && $departmentId) {
                $department = Department::find($departmentId);
                
                if (!$department) {
                    $validator->errors()->add(
                        'department_id',
                        'The selected department does not exist.'
                    );
                    return;
                }

                $effectiveFacultyId = $this->has('faculty_id') ? $facultyId : $call->faculty_id;
                
                if (!$effectiveFacultyId) {
                    $validator->errors()->add(
                        'department_id',
                        'Department can only be selected when faculty is specified.'
                    );
                    return;
                }

                if ($department->faculty_id != $effectiveFacultyId) {
                    $validator->errors()->add(
                        'department_id',
                        'The selected department must belong to the call\'s faculty.'
                    );
                    return;
                }
            }

            // Validate research center belongs to call's university
            if ($this->has('research_center_id') && $researchCenterId) {
                $center = ResearchCenter::find($researchCenterId);
                
                if (!$center) {
                    $validator->errors()->add(
                        'research_center_id',
                        'The selected research center does not exist.'
                    );
                    return;
                }

                if ($center->parent_university_id != $call->university_id) {
                    $validator->errors()->add(
                        'research_center_id',
                        'The selected research center must belong to the call\'s university.'
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
            'closes_at.after' => 'The closing date must be after the opening date.',
        ];
    }
}

