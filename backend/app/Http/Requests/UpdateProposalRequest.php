<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'call_id'                   => 'nullable|exists:calls,id',
            'type_id'                   => 'nullable|exists:proposal_types,id',
            'title'                     => 'sometimes|string|max:500',
            'abstract'                  => 'sometimes|string',
            'objectives'                => 'sometimes|string',
            'methodology'               => 'sometimes|string',
            'keywords'                  => 'sometimes|string',
            'budget'                    => 'sometimes|numeric|min:0',
            'budget_allocation'         => 'nullable|json',
            'status_change_comment'     => 'nullable|string',
            'academic_year_id'          => 'nullable|exists:academic_years,id',
            'file_id'                   => 'nullable|exists:files,id',
            'ethics_file_id'            => 'nullable|exists:files,id',
            'ethics_approval_status_id' => 'nullable|exists:ethics_approval_statuses,id',
        ];
    }
}
