<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OutputRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:500',
            'description' => 'nullable|string',
            'output_type_id' => 'required|exists:output_types,id',
            'file_id' => 'nullable|exists:files,id',
            'status_id' => 'nullable|integer',
        ];
    }
}
