<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required',
            'proposal_id' => 'nullable|exists:proposals,id',
            'total_budget' => 'required|numeric',
        ];
    }
}
