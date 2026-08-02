<?php

namespace App\Http\Requests;

use App\Models\Campus;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\University;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreResearchCenterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by the policy
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:research_centers,code',
            'description' => 'nullable|string',
            'director_id' => 'nullable|exists:users,id',
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'parent_university_id' => 'required|exists:universities,id',
            'parent_campus_id' => 'nullable|exists:campuses,id',
            'parent_faculty_id' => 'nullable|exists:faculties,id',
            'parent_department_id' => 'nullable|exists:departments,id',
        ];
    }

    /**
     * Configure the validator instance.
     * Add tenant-aware hierarchy validation.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $user = $this->user();
            $universityId = $this->input('parent_university_id');
            $campusId = $this->input('parent_campus_id');
            $facultyId = $this->input('parent_faculty_id');
            $departmentId = $this->input('parent_department_id');
            $directorId = $this->input('director_id');

            // ✅ Validate university belongs to user's institution
            if ($universityId && $user->university_id && $universityId != $user->university_id) {
                $validator->errors()->add(
                    'parent_university_id',
                    'You can only create research centers within your own university.'
                );
                return;
            }

            // ✅ Validate campus belongs to specified university
            if ($campusId) {
                $campus = Campus::find($campusId);
                if (!$campus || $campus->university_id != $universityId) {
                    $validator->errors()->add(
                        'parent_campus_id',
                        'The selected campus must belong to the selected university.'
                    );
                    return;
                }
            }

            // ✅ Validate faculty belongs to specified campus
            if ($facultyId) {
                if (!$campusId) {
                    $validator->errors()->add(
                        'parent_faculty_id',
                        'Faculty can only be selected when campus is specified.'
                    );
                    return;
                }

                $faculty = Faculty::find($facultyId);
                if (!$faculty || $faculty->campus_id != $campusId) {
                    $validator->errors()->add(
                        'parent_faculty_id',
                        'The selected faculty must belong to the selected campus.'
                    );
                    return;
                }
            }

            // ✅ Validate department belongs to specified faculty
            if ($departmentId) {
                if (!$facultyId) {
                    $validator->errors()->add(
                        'parent_department_id',
                        'Department can only be selected when faculty is specified.'
                    );
                    return;
                }

                $department = Department::find($departmentId);
                if (!$department || $department->faculty_id != $facultyId) {
                    $validator->errors()->add(
                        'parent_department_id',
                        'The selected department must belong to the selected faculty.'
                    );
                    return;
                }
            }

            // ✅ Validate director belongs to same university
            if ($directorId) {
                $director = \App\Models\User::find($directorId);
                $directorUniversityId = $director->university_id 
                    ?: $director->department?->faculty?->campus?->university_id;
                    
                if (!$director || $directorUniversityId != $universityId) {
                    $validator->errors()->add(
                        'director_id',
                        'The selected director must belong to the same university.'
                    );
                }
            }
        });
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Research center name is required.',
            'code.required' => 'Research center code is required.',
            'code.unique' => 'This research center code is already in use.',
            'parent_university_id.required' => 'University is required.',
            'parent_university_id.exists' => 'The selected university does not exist.',
        ];
    }
}
