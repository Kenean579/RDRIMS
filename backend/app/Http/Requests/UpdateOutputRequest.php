<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOutputRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'sometimes|exists:output_categories,id',
            'student_level_id' => 'nullable|exists:student_levels,id',
            'subtype_id' => 'sometimes|exists:output_subtypes,id',
            'proposal_id' => 'nullable|exists:proposals,id',
            'title' => 'sometimes|string|max:255',
            'abstract' => 'nullable|string',
            'partner_id' => 'nullable|exists:partners,id',
            'project_id' => 'nullable|exists:projects,id',
            'status_id' => 'sometimes|exists:output_statuses,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'academic_year_id' => 'sometimes|exists:academic_years,id',
            'budget' => 'nullable|numeric|min:0',
            'research_center_id' => 'nullable|exists:research_centers,id',
        ];
    }
}
