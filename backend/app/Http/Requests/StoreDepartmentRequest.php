<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->has('faculty_id')) {
            $this->merge([
                'faculty_id' => (int) $this->faculty_id,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:departments,code',
            'faculty_id' => 'required|integer|exists:faculties,id',
            'logo_file_id' => 'nullable|exists:files,id',
        ];
    }
}
