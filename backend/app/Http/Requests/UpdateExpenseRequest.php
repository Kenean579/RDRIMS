<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => 'sometimes|exists:projects,id',
            'amount' => 'sometimes|numeric|min:0',
            'budget_category' => 'nullable|in:personnel,equipment,travel,other',
            'description' => 'sometimes|string',
            'research_center_id' => 'nullable|exists:research_centers,id',
        ];
    }
}