<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEthicsRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'proposal_id' => 'sometimes|exists:proposals,id',
            'submitted_to_irb' => 'nullable|boolean',
            'approval_status_id' => 'sometimes|exists:ethics_approval_statuses,id',
            'comments' => 'nullable|string',
            'version' => 'nullable|integer',
        ];
    }
}
