<?php

namespace App\Http\Requests;

use App\Models\Faculty;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateDepartmentRequest extends FormRequest
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
        $departmentId = $this->route('department')?->id;

        return [
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:50|unique:departments,code,' . $departmentId,
            'faculty_id' => 'sometimes|required|exists:faculties,id',
            'logo_file_id' => 'nullable|exists:files,id',
        ];
    }

    /**
     * Configure the validator instance.
     * Add tenant-aware validation and prevent faculty_id modification.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $user = $this->user();
            $facultyId = $this->input('faculty_id');

            // Prevent changing faculty_id (IDOR protection)
            if ($facultyId) {
                $validator->errors()->add(
                    'faculty_id',
                    'The faculty cannot be changed after creation.'
                );
                return;
            }

            // If somehow faculty_id is provided, validate tenant ownership
            if ($facultyId && $user) {
                $faculty = Faculty::find($facultyId);

                if (!$faculty || $faculty->campus?->university_id !== $user->university_id) {
                    $validator->errors()->add(
                        'faculty_id',
                        'The selected faculty does not belong to your university.'
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
            'name.required' => 'Department name is required.',
            'code.required' => 'Department code is required.',
            'code.unique' => 'This department code is already in use.',
            'faculty_id.exists' => 'The selected faculty does not exist.',
        ];
    }
}
