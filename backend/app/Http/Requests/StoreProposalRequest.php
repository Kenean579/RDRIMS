<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'call_id'                   => 'required|exists:calls,id',
            'type_id'                   => 'required|exists:proposal_types,id',
            'title'                     => 'required|string|max:500',
            'abstract'                  => 'required|string',
            'objectives'                => 'required|string',
            'methodology'               => 'required|string',
            'keywords'                  => 'required|string',
            'budget'                    => 'required|numeric|min:0',
            'budget_allocation'         => 'nullable|json',
            'status_change_comment'     => 'nullable|string',
            'academic_year_id'          => 'required|exists:academic_years,id',
            'file_id'                   => 'nullable|exists:files,id',
            'ethics_file_id'            => 'nullable|exists:files,id',
            'ethics_approval_status_id' => 'nullable|exists:ethics_approval_statuses,id',
            'submitted_at' => 'sometimes|date',
        ];
    }

    public function messages(): array
    {
        return [
            'call_id.required' => 'Please select a valid call for proposals.',
            'title.required' => 'The proposal title is required.',
            'budget.numeric' => 'The budget must be a numeric value.',
        ];
    }
}
