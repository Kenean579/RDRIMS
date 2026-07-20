<?php

namespace App\Http\Requests\Call;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Call;

class UpdateCallRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $call = $this->route('call');
        return $this->user()->can('update', $call);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $user = $this->user();
        $universityId = $user->university_id;
        return [
            'title'               => ['sometimes', 'string', 'max:255'],
            'description'         => ['sometimes', 'string'],
            'deadline'            => ['sometimes', 'date'],
            'thematic_areas'      => ['sometimes', 'string'],
            'status_id'           => ['sometimes', 'exists:call_statuses,id'],
            'academic_year_id'    => ['sometimes', 'nullable', 'exists:academic_years,id'],
            'guideline_file_id'   => ['sometimes', 'nullable', 'exists:files,id'],
            'research_center_id'  => ['sometimes', 'nullable', 'exists:research_centers,id', 'exists:research_centers,university_id,' . $universityId],
            'campus_id'           => ['sometimes', 'nullable', 'exists:campuses,id', 'exists:campuses,university_id,' . $universityId],
            'faculty_id'          => ['sometimes', 'nullable', 'exists:faculties,id', 'exists:faculties,university_id,' . $universityId],
            'department_id'       => ['sometimes', 'nullable', 'exists:departments,id', 'exists:departments,university_id,' . $universityId],
            'is_public'           => ['sometimes', 'boolean'],
            'is_featured'         => ['sometimes', 'boolean'],
            'metadata'            => ['sometimes', 'array'],
        ];
    }
}
