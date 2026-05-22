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
            'title' => 'sometimes',
            'abstract' => 'sometimes',
            'call_id' => 'sometimes|exists:calls,id',
            'budget' => 'sometimes|numeric',
        ];
    }
}
