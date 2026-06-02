<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'deadline' => 'required|date',
            'budget_limit' => 'nullable|numeric|min:0',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'status_id' => 'nullable|exists:call_statuses,id',
            'thematic_areas' => 'nullable|string',
            'guideline_file_id' => 'nullable|exists:files,id',
        ];
    }
}
