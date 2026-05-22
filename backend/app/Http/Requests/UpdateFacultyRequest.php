<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFacultyRequest extends FormRequest
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
            'campus_id' => 'sometimes|exists:campuses,id',
        ];
    }
}
