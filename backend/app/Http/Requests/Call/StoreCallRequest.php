<?php

namespace App\Http\Requests\Call;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Call;

class StoreCallRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Policy will handle the actual permission check
        return $this->user()->can('create', Call::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $user = $this->user();
        $universityId = $user->university_id;
        return [
            'title'               => ['required', 'string', 'max:255'],
            'description'         => ['required', 'string'],
            'deadline'            => ['required', 'date'],
            'thematic_areas'      => ['nullable', 'string'],
            'status_id'           => ['required', 'exists:call_statuses,id'],
            'academic_year_id'    => ['nullable', 'exists:academic_years,id'],
            'guideline_file_id'   => ['nullable', 'exists:files,id'],
            'research_center_id'  => ['nullable', 'exists:research_centers,id', 'exists:research_centers,university_id,' . $universityId],
            'campus_id'           => ['nullable', 'exists:campuses,id', 'exists:campuses,university_id,' . $universityId],
            'faculty_id'          => ['nullable', 'exists:faculties,id', 'exists:faculties,university_id,' . $universityId],
            'department_id'       => ['nullable', 'exists:departments,id', 'exists:departments,university_id,' . $universityId],
            'is_public'           => ['sometimes', 'boolean'],
            'is_featured'         => ['sometimes', 'boolean'],
            'metadata'            => ['sometimes', 'array'],
        ];
    }
}
