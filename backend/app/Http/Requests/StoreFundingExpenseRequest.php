<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFundingExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'budget_category_id' => 'required|exists:budget_categories,id',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'reference_number' => 'required|string|max:100|unique:funding_expenses,reference_number',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'expense_date' => 'required|date',
        ];
    }

    public function messages(): array
    {
        return [
            'reference_number.unique' => 'This expense reference number already exists.',
        ];
    }
}
