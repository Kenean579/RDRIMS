<?php
// app/Http/Requests/StoreCallRequest.php

namespace App\Http\Requests\Call;

use App\Models\Call;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Call::class);
    }

    protected function prepareForValidation(): void
    {
        // Ensure status is set (default to 'open' if not provided)
        if (!$this->has('status_name') && !$this->has('status_id')) {
            $this->merge(['status_name' => 'open']);
        }
    }

    public function rules(): array
    {
        return [
            // Basic fields
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'deadline' => 'required|date|after:today',
            'thematic_areas' => 'required|string',
            'budget_limit' => 'nullable|numeric|min:0',

            // Status – accept either name or ID
            'status_name' => 'sometimes|string|exists:call_statuses,name',
            'status_id' => 'sometimes|exists:call_statuses,id',

            // Academic Year
            'academic_year_id' => 'nullable|exists:academic_years,id',

            // File
            'guideline_file_id' => 'nullable|exists:files,id',

            // Hierarchy
            'university_id' => 'nullable|exists:universities,id',
            'campus_id' => 'nullable|exists:campuses,id',
            'faculty_id' => 'nullable|exists:faculties,id',
            'department_id' => 'nullable|exists:departments,id',
            'research_center_id' => 'nullable|exists:research_centers,id',

            // Meta
            'is_public' => 'boolean',
            'is_featured' => 'boolean',
            'metadata' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'deadline.after' => 'The deadline must be a future date.',
        ];
    }
}
