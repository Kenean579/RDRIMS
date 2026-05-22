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
            'name' => 'sometimes',
            'code' => 'sometimes|sometimes',
            'faculty_id' => 'sometimes|exists:faculties,id',
        ];
    }
}
