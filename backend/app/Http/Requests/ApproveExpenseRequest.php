<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('finance_officer', 'super_admin');
    }

    public function rules(): array
    {
        return [];
    }
}
