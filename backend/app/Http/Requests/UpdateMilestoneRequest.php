<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMilestoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');
        return $this->user()->can('update', $project);
    }

    public function rules(): array
    {
        return [
            'title'         => 'sometimes|string|max:255',
            'description'   => 'nullable|string',
            'due_date'      => 'sometimes|date',
            'display_order' => 'nullable|integer|min:0',
            'status_id'     => 'nullable|exists:milestone_statuses,id',
        ];
    }
}