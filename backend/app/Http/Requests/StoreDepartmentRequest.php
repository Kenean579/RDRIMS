<?php

namespace App\Http\Requests;

use App\Models\Faculty;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreDepartmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by the policy
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('faculty_id')) {
            $this->merge([
                'faculty_id' => (int) $this->faculty_id,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:departments,code',
            'faculty_id' => 'required|integer|exists:faculties,id',
            'logo_file_id' => 'nullable|exists:files,id',
        ];
    }

    /**
     * Configure the validator instance.
     * Add tenant-aware validation to ensure faculty_id belongs to user's university.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $user = $this->user();
            $facultyId = $this->input('faculty_id');

            if ($facultyId && $user) {
                $faculty = Faculty::find($facultyId);

                // Ensure the faculty belongs to the user's university (tenant isolation)
                // Faculty → Campus → University
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
            'faculty_id.required' => 'A faculty must be selected.',
            'faculty_id.exists' => 'The selected faculty does not exist.',
            'code.unique' => 'This department code is already in use.',
        ];
    }
}
