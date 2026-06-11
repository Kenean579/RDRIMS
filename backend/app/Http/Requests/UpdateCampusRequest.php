<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCampusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:50|unique:campuses,code,' . $this->route('campus')->id,
            'university_id' => 'sometimes|required|exists:universities,id',
            'logo_file_id' => 'nullable|exists:files,id',
        ];
    }
}
