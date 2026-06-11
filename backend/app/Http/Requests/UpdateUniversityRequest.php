<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\University;

class UpdateUniversityRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('update', $this->route('university')); }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:50|unique:universities,code,' . $this->route('university')->id,
            'location' => 'nullable|string|max:255',
            'logo_file_id' => 'nullable|exists:files,id',
        ];
    }
}
