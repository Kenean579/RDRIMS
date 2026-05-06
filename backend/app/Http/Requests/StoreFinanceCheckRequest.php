<?php
// app/Http/Requests/FinanceCheckRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FinanceCheckRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->hasRole('finance_officer') || $this->user()->hasRole('admin');
    }

    public function rules()
    {
        return [
            'status_name' => 'required|string|exists:finance_check_statuses,name',
            'comments'    => 'nullable|string',
        ];
    }
}
