<?php
// app/Http/Requests/StoreCallRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation rules for storing a new call.
 */
class StoreCallRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Only admin or research office staff can create calls.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && ($user->hasRole('admin') || $user->hasRole('research_admin'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title'            => 'required|string|max:255',
            'description'      => 'required|string',
            'deadline'         => 'required|date|after:today',
            'thematic_areas'   => 'required|string',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'status_id'        => 'sometimes|exists:call_statuses,id',
            'guideline_file'   => 'nullable|file|max:10240', // Max 10MB
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Default status to 'draft' if not provided
        if (!$this->has('status_id')) {
            $this->merge(['status_id' => 1]);
        }
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
