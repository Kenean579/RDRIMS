<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|unique:academic_years,name,' . $this->route('academic_year')->id,
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date',
            'is_current' => 'sometimes|boolean',
        ];
    }
}
