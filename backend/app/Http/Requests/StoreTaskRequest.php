<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'milestone_id'   => 'nullable|exists:milestones,id',
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'assigned_to'    => 'nullable|exists:users,id',
            'due_date'       => 'nullable|date',
            'estimated_hours'=> 'nullable|integer|min:0',
            'actual_hours'   => 'nullable|integer|min:0',
            'status_id'      => 'required|exists:task_statuses,id'
        ];
    }
}