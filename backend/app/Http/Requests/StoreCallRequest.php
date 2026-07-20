<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCallRequest extends FormRequest
{
    /**
     * Determine whether the user can create a call.
     * Authorization is enforced by CallPolicy.
     */
    public function authorize(): bool
    {
        return true;
    }
   /**
    * prepare data before validation
    */

       protected function prepareForValidation(): void
   {
    $this->merge([
        'title' => trim((string) $this->title),
        'description' => trim((string) $this->description),
        'thematic_areas' => trim((string) $this->thematic_areas),
    ]);
  }

    /**
     * Validation rules for creating a call.
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
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'required',
                'string',
            ],

            'thematic_areas' => [
                'required',
                'string',
            ],

            /*
            |--------------------------------------------------------------------------
            | Dates
            |--------------------------------------------------------------------------
            */

            'deadline' => [
                'required',
                'date',
                'after:today',
            ],

            'published_at' => [
                'nullable',
                'date',
            ],

            'opens_at' => [
                'nullable',
                'date',
            ],

            'closes_at' => [
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
                'nullable',
                Rule::exists('academic_years', 'id'),
            ],

            'status_id' => [
                'nullable',
                Rule::exists('call_statuses', 'id'),
            ],

            'guideline_file_id' => [
                'nullable',
                Rule::exists('files', 'id'),
            ],

            /*
            |--------------------------------------------------------------------------
            |Institutional  Hierarchy
            |--------------------------------------------------------------------------
            */

            'university_id' => [
                'nullable',
                Rule::exists('universities', 'id'),
            ],

            'research_center_id' => [
                'nullable',
                Rule::exists('research_centers', 'id'),
            ],

            'campus_id' => [
                'nullable',
                Rule::exists('campuses', 'id'),
            ],

            'faculty_id' => [
                'nullable',
                Rule::exists('faculties', 'id'),
            ],

            'department_id' => [
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
            'deadline.after' => 'The application deadline must be a future date.',
            'closes_at.after' => 'The closing date must be after the opening date.',
        ];
    }

}
