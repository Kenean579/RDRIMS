<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CallRequest extends FormRequest
{
    use \App\Traits\CastBooleanFields;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->castBooleans(['is_active']);
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'academic_year_id' => 'required|exists:academic_years,id',
            'call_type_id' => 'required|exists:call_types,id',
            'deadline' => 'required|date|after:now',
            'max_budget' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ];
        'message' = 'MELIKT',

    }
}
