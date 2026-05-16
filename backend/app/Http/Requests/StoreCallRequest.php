<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCallRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'             => 'required|string|max:255',
            'description'       => 'required|string',
            'deadline'          => 'required|date|after:now',
            'thematic_areas'    => 'required|string',
            'status_id'         => 'required|exists:call_statuses,id',
            'academic_year_id'  => 'nullable|exists:academic_years,id',
            'guideline_file_id' => 'nullable|exists:files,id',
        ];
    }
}
