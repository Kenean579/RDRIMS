<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'proposal_id' => 'sometimes|exists:proposals,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'total_budget' => 'sometimes|numeric|min:0',
            'budget_allocation' => 'nullable|array',
            'status_id' => 'sometimes|exists:project_statuses,id',
            'pi_id' => 'sometimes|exists:users,id',
            'academic_year_id' => 'sometimes|exists:academic_years,id',
            'cover_image_id' => 'nullable|exists:files,id',
            'research_center_id' => 'nullable|exists:research_centers,id',
        ];
    }
}
