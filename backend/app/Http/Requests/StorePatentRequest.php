<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'inventors' => 'nullable|string',
            'filing_date' => 'nullable|date',
            'patent_number' => 'required|string|max:100',
            'status_id' => 'nullable|exists:patent_statuses,id',
        ];
    }
}
