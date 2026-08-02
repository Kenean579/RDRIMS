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
    }

    public function rules(): array
    {
        return [
            'title'        => 'required|string|max:255|min:5',
            'abstract'     => 'required|string|min:20',
            'call_id'      => 'nullable|exists:calls,id',
            'type_id'      => 'nullable|exists:proposal_types,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'budget'       => 'required|numeric|min:1',
            'keywords'     => 'nullable|string|max:500',
            'objectives'   => 'nullable|string|min:20',
            'methodology'  => 'nullable|string|min:20',
            'proposal_file' => 'nullable|file|mimes:pdf,doc,docx|max:20480',
            'investigators'          => 'nullable|array',
            'investigators.*.user_id'  => 'nullable|exists:users,id',
            'investigators.*.name'     => 'nullable|string|max:255',
            'investigators.*.email'    => 'nullable|email',
            'investigators.*.role_id'  => 'required_with:investigators|exists:investigator_roles,id',
        ];
    }

    public function messages(): array
    {
        return [
            'proposal_file.file' => 'The proposal document must be uploaded as a file.',
            'proposal_file.mimes' => 'The proposal document must be a PDF, DOC, or DOCX file.',
            'proposal_file.max' => 'The proposal document must not exceed 20 MB.',
        ];
    }
}
