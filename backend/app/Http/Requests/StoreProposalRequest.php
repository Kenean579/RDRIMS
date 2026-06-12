<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // The frontend sends investigators as a JSON string inside FormData
        if (is_string($this->investigators)) {
            $this->merge([
                'investigators' => json_decode($this->investigators, true) ?? [],
            ]);
        }
        
        // Also decode budget allocation if it's sent as a JSON string
        if (is_string($this->budget_allocation)) {
            $this->merge([
                'budget_allocation' => json_decode($this->budget_allocation, true) ?? [],
            ]);
        }
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        \Illuminate\Support\Facades\Log::error('Validation failed Proposal: ', $validator->errors()->toArray());
        parent::failedValidation($validator);
    }

    public function rules(): array
    {
        return [
            'title'        => 'required|string|max:255',
            'abstract'     => 'required|string',
            'call_id'      => 'nullable|exists:calls,id',
            'type_id'      => 'nullable|exists:proposal_types,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'budget'       => 'required|numeric|min:0',
            'keywords'     => 'nullable|string',
            'objectives'   => 'nullable|string',
            'methodology'  => 'nullable|string',
            'proposal_file' => 'nullable|file|mimes:pdf,doc,docx|max:20480',
            'ethics_file'  => 'nullable|file|mimes:pdf,doc,docx|max:20480',
            'budget_allocation' => 'nullable|array',
            'investigators'          => 'nullable|array',
            'investigators.*.user_id'  => 'nullable|exists:users,id',
            'investigators.*.name'     => 'nullable|string',
            'investigators.*.email'    => 'nullable|email',
            'investigators.*.role_id'  => 'required_with:investigators|exists:investigator_roles,id',
        ];
    }
}
