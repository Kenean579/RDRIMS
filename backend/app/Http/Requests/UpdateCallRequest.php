<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCallRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'             => 'sometimes|string|max:255',
            'description'       => 'sometimes|string',
            'deadline'          => 'sometimes|date',
            'thematic_areas'    => 'sometimes|string',
            'status_id'         => 'sometimes|exists:call_statuses,id',
            'academic_year_id'  => 'nullable|exists:academic_years,id',
            'guideline_file_id' => 'nullable|exists:files,id',
        ];
    }
}
