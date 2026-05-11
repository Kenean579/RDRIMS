<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOutputRequest extends FormRequest
{
    public function authorize(): bool
    {
        $output = $this->route('output');
        return $this->user()->can('update', $output);
    }

    public function rules(): array
    {
        return [
            'category_id'       => 'sometimes|exists:output_categories,id',
            'student_level_id'  => 'nullable|exists:student_levels,id',
            'subtype_id'        => 'sometimes|exists:output_subtypes,id',
            'proposal_id'       => 'nullable|exists:proposals,id',
            'title'             => 'sometimes|string|max:255',
            'abstract'          => 'nullable|string',
            'partner_id'        => 'nullable|exists:partners,id',
            'project_id'        => 'nullable|exists:projects,id',
            'start_date'        => 'nullable|date',
            'end_date'          => 'nullable|date|after_or_equal:start_date',
            'feedback'          => 'nullable|string',
            'academic_year_id'  => 'nullable|exists:academic_years,id',
            'budget'            => 'nullable|numeric|min:0',
        ];
    }
}