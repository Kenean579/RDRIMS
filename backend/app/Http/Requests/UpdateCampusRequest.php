<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCampusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'code' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('campuses', 'code')->ignore($this->route('campus')),
            ],

            'university_id' => [
                'sometimes',
                'nullable',
                'exists:universities,id',
            ],

            'logo_file_id' => [
                'nullable',
                'exists:files,id',
            ],
        ];
    }
}
