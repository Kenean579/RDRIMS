<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\AcademicYear;

class UpdateAcademicYearRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('update', $this->route('academic_year')); }

    public function rules(): array
    {
        return [
            'name'       => 'required|string|max:50|unique:academic_years,name,' . $this->route('academic_year')->id,
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
            'is_current' => 'boolean',
        ];
    }
}
