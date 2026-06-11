<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'amount' => 'required|numeric|min:0',
            'budget_category' => 'nullable|in:personnel,equipment,travel,other',
            'description' => 'required|string',
            'research_center_id' => 'nullable|exists:research_centers,id',
        ];
    }
}