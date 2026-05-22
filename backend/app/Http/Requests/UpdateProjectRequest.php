<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes',
            'proposal_id' => 'nullable|exists:proposals,id',
            'total_budget' => 'sometimes|numeric',
        ];
    }
}
