<?php

namespace App\Http\Requests;

use App\Models\Campus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreFacultyRequest extends FormRequest
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
            'code' => 'required|string|max:50|unique:faculties,code',
            'campus_id' => 'required|exists:campuses,id',
            'logo_file_id' => 'nullable|exists:files,id',
        ];
    }

    /**
     * Configure the validator instance.
     * Add tenant-aware validation to ensure campus_id belongs to user's university.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $user = $this->user();
            $campusId = $this->input('campus_id');

            if ($campusId && $user) {
                $campus = Campus::find($campusId);

                // Ensure the campus belongs to the user's university (tenant isolation)
                if (!$campus || $campus->university_id !== $user->university_id) {
                    $validator->errors()->add(
                        'campus_id',
                        'The selected campus does not belong to your university.'
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
            'campus_id.required' => 'A campus must be selected.',
            'campus_id.exists' => 'The selected campus does not exist.',
            'code.unique' => 'This faculty code is already in use.',
        ];
    }
}
