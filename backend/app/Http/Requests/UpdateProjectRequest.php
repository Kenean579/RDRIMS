<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');
        return $this->user()->can('update', $project);
    }

    public function rules(): array
    {
        return [
            'title'            => 'sometimes|string|max:255',
            'start_date'       => 'sometimes|date',
            'end_date'         => 'sometimes|date|after:start_date',
            'total_budget'     => 'sometimes|numeric|min:0',
            'budget_allocation'=> 'nullable|array',
            'status_id'        => 'sometimes|exists:project_statuses,id',
            'pi_id'            => 'sometimes|exists:users,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
        ];
    }
}