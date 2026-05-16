<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOutputRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id'      => 'required|exists:output_categories,id',
            'student_level_id' => 'nullable|exists:student_levels,id',
            'subtype_id'       => 'required|exists:output_subtypes,id',
            'proposal_id'      => 'nullable|exists:proposals,id',
            'title'            => 'required|string|max:500',
            'abstract'         => 'required|string',
            'partner_id'       => 'nullable|exists:partners,id',
            'project_id'       => 'nullable|exists:projects,id',
            'start_date'       => 'nullable|date',
            'end_date'         => 'nullable|date|after_or_equal:start_date',
            'feedback'         => 'nullable|string',
            'academic_year_id' => 'required|exists:academic_years,id',
            'budget'           => 'nullable|numeric|min:0',
        ];
    }
}