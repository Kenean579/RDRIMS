<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMilestoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'due_date' => 'required|date',
            'description' => 'nullable|string',
            'display_order' => 'nullable|integer',
            'status_id' => 'nullable|exists:milestone_statuses,id',
        ];
    }
}
