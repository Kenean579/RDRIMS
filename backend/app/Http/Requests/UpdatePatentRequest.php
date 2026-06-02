<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => 'sometimes|exists:projects,id',
            'title' => 'sometimes|string|max:255',
            'inventors' => 'nullable|string',
            'filing_date' => 'nullable|date',
            'application_number' => 'sometimes|string|max:100',
            'status_id' => 'sometimes|exists:patent_statuses,id',
        ];
    }
}
