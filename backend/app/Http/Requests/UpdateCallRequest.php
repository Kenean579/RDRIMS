<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'closes_at.after' => 'The closing date must be after the opening date.',
        ];
    }
}
