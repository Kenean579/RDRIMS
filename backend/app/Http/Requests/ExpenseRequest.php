<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseRequest extends FormRequest
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
            'expense_type_id' => 'required|exists:expense_types,id',
            'description' => 'required|string',
            'expense_date' => 'required|date',
            'receipt_file_id' => 'nullable|exists:files,id',
        ];
    }
}
