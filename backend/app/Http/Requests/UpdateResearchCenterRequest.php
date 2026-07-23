<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateResearchCenterRequest extends FormRequest
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
        $centerid = $this->route('research_center')?->id;

        return [
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:50|unique:research_centers,code,' . $centerid,
            'description' => 'nullable|string',
            'director_id' => 'nullable|exists:users,id',
            'logo_file_id' => 'nullable|exists:files,id',
            'parent_university_id' => 'sometimes|exists:universities,id',
            'parent_campus_id' => 'nullable|sometimes|exists:campuses,id',
            'parent_faculty_id' => 'nullable|sometimes|exists:faculties,id',
            'parent_department_id' => 'nullable|sometimes|exists:departments,id',
        ];
    }

    /**
     * Configure the validator instance.
     * Prevent hierarchy modification (IDOR protection).
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $user = $this->user();

            // ✅ Prevent changing hierarchy (immutability)
            if ($this->has('parent_university_id')) {
                $validator->errors()->add(
                    'parent_university_id',
                    'The university cannot be changed after creation.'
                );
            }

            if ($this->has('parent_campus_id')) {
                $validator->errors()->add(
                    'parent_campus_id',
                    'The campus cannot be changed after creation.'
                );
            }

            if ($this->has('parent_faculty_id')) {
                $validator->errors()->add(
                    'parent_faculty_id',
                    'The faculty cannot be changed after creation.'
                );
            }

            if ($this->has('parent_department_id')) {
                $validator->errors()->add(
                    'parent_department_id',
                    'The department cannot be changed after creation.'
                );
            }

            // ✅ Validate director belongs to same university
            $directorId = $this->input('director_id');
            if ($directorId) {
                $director = \App\Models\User::find($directorId);
                $centerUniversityId = $this->route('research_center')->parent_university_id;
                $directorUniversityId = $director->university_id 
                    ?: $director->department?->faculty?->campus?->university_id;
                    
                if (!$director || $directorUniversityId != $centerUniversityId) {
                    $validator->errors()->add(
                        'director_id',
                        'The selected director must belong to the same university as this research center.'
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
        ];
    }
}
