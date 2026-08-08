<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMilestoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => 'sometimes|exists:projects,id',
            'title' => 'sometimes|string|max:255',
            'due_date' => 'sometimes|date',
            'description' => 'nullable|string',
            'display_order' => 'nullable|integer',
            'status_id' => 'nullable|exists:milestone_statuses,id',
        ];
    }
}
