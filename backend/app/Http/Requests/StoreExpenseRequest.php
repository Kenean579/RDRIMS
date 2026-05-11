<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');
        return $this->user()->can('update', $project);
    }

    public function rules(): array
    {
        return [
            'amount'          => 'required|numeric|min:0',
            'budget_category' => 'nullable|string|max:50',
            'description'     => 'required|string',
        ];
    }
}