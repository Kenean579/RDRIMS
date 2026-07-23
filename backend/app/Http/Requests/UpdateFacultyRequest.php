<?php

namespace App\Http\Requests;

use App\Models\Campus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateFacultyRequest extends FormRequest
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
        $facultyId = $this->route('faculty')?->id;

        return [
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:50|unique:faculties,code,' . $facultyId,
            'campus_id' => 'sometimes|required|exists:campuses,id',
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

            // Prevent changing campus_id (IDOR protection)
            if ($campusId) {
                $validator->errors()->add(
                    'campus_id',
                    'The campus cannot be changed after creation.'
                );
                return;
            }

            // If somehow campus_id is provided, validate tenant ownership
            if ($campusId && $user) {
                $campus = Campus::find($campusId);

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
            'name.required' => 'Faculty name is required.',
            'code.required' => 'Faculty code is required.',
            'code.unique' => 'This faculty code is already in use.',
            'campus_id.exists' => 'The selected campus does not exist.',
        ];
    }
}
