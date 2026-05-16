<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PatentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:500',
            'patent_number' => 'required|string|unique:patents,patent_number,' . ($this->patent?->id ?? ''),
            'filing_date' => 'required|date',
            'grant_date' => 'nullable|date|after:filing_date',
            'inventors' => 'required|string',
            'status' => 'required|in:pending,granted,expired',
            'project_id' => 'nullable|exists:projects,id',
        ];
    }
}
