<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFundingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'funding_source_id' => 'required|exists:funding_sources,id',
            'project_id' => 'nullable|exists:projects,id',
            'proposal_id' => 'nullable|exists:proposals,id',
            'reference_number' => 'required|string|max:100|unique:fundings,reference_number',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'total_amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'start_date' => 'required|date|before:end_date',
            'end_date' => 'required|date|after:start_date',
            'is_internal' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'reference_number.unique' => 'This funding reference number already exists.',
            'start_date.before' => 'Start date must be before end date.',
            'end_date.after' => 'End date must be after start date.',
        ];
    }
}
