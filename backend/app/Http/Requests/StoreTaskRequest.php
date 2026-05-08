<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $milestone = $this->route('milestone');
        $project = $milestone->project;
        return $this->user()->can('update', $project);
    }

    public function rules(): array
    {
        return [
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string',
            'estimated_hours' => 'nullable|integer|min:0',
            'actual_hours'    => 'nullable|integer|min:0',
            'assigned_to'     => 'nullable|exists:users,id',
            'due_date'        => 'required|date',
            'status_id'       => 'nullable|exists:task_statuses,id',
        ];
    }
}