<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:500',
            'abstract' => 'required|string',
            'call_id' => 'required|exists:calls,id',
            'budget' => 'required|numeric|min:0',
            'duration_months' => 'required|integer|min:1',
            'keywords' => 'nullable|string',
            'thematic_area_id' => 'required|exists:thematic_areas,id',
            'proposal_file_id' => 'nullable|exists:files,id',
            'status_id' => 'nullable|integer',
        ];
    }
}
