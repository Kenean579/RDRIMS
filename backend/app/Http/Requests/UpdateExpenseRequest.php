<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $expense = $this->route('expense');
        return $this->user()->can('update', $expense);
    }

    public function rules(): array
    {
        return [
            'amount'          => 'sometimes|numeric|min:0',
            'budget_category' => 'nullable|string|max:50',
            'description'     => 'sometimes|string',
        ];
    }
}