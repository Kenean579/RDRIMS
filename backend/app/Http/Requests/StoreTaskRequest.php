<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'milestone_id' => 'required|exists:milestones,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status_id' => 'nullable|exists:task_statuses,id',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
        ];
    }
}
