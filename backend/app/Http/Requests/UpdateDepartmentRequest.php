<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:50|unique:departments,code,' . $this->route('department')->id,
            'faculty_id' => 'sometimes|required|exists:faculties,id',
            'logo_file_id' => 'nullable|exists:files,id',
        ];
    }
}
