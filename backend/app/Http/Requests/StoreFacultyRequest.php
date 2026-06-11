<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFacultyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:faculties,code',
            'campus_id' => 'required|exists:campuses,id',
            'logo_file_id' => 'nullable|exists:files,id',
        ];
    }
}
