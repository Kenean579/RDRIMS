<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEthicsRequestRequest extends FormRequest
{
    use \App\Traits\CastBooleanFields;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->castBooleans(['submitted_to_irb']);
    }

    public function rules(): array
    {
        return [
            'proposal_id' => 'required|exists:proposals,id',
            'submitted_to_irb' => 'nullable|boolean',
            'approval_status_id' => 'nullable|exists:ethics_approval_statuses,id',
            'comments' => 'nullable|string',
            'version' => 'nullable|integer',
        ];
    }
}
