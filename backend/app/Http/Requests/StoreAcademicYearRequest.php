<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAcademicYearRequest extends FormRequest
{
    use \App\Traits\CastBooleanFields;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->castBooleans(['is_current']);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|unique:academic_years',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'is_current' => 'boolean',
        ];
    }
}
