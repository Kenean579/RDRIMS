<?php
// app/Http/Requests/UpdateCallRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation rules for updating an existing call.
 */
class UpdateCallRequest extends FormRequest
{
    /**
     * Determine if the user is authorized.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && ($user->hasRole('admin') || $user->hasRole('research_admin'));
    }

    /**
     * Get the validation rules.
     */
    public function rules(): array
    {
        return [
            'title'            => 'sometimes|string|max:255',
            'description'      => 'sometimes|string',
            'deadline'         => 'sometimes|date|after:today',
            'thematic_areas'   => 'sometimes|string',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'status_id'        => 'sometimes|exists:call_statuses,id',
            'guideline_file'   => 'nullable|file|max:10240',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'deadline.after' => 'The deadline must be a future date.',
            'guideline_file.max' => 'The guideline file cannot exceed 10MB.',
        ];
    }
}
