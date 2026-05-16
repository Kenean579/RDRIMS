<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewCriterionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'max_score' => 'required|numeric|min:1',
            'weight' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'call_type_id' => 'required|exists:call_types,id',
        ];
    }
}
