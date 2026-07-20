<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCampusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:campuses,code',
            'university_id' => 'required|exists:universities,id',
            'logo_file_id' => 'nullable|exists:files,id',
        ];
    }
}
