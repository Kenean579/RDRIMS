<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:departments,code,' . ($this->department?->id ?? ''),
            'college_id' => 'required|exists:colleges,id',
            'description' => 'nullable|string',
        ];
    }
}
