<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'proposal_id' => 'nullable|exists:proposals,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'total_budget' => 'required|numeric|min:0',
            'budget_allocation' => 'nullable|array',
            'status_id' => 'nullable|exists:project_statuses,id',
            'pi_id' => 'nullable|exists:users,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'cover_image_id' => 'nullable|exists:files,id',
        ];
    }
}
