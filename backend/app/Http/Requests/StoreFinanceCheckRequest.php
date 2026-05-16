<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFinanceCheckRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'proposal_id' => 'required|exists:proposals,id',
            'status_id'   => 'required|exists:finance_check_statuses,id',
            'comments'    => 'nullable|string'
        ];
    }
}
