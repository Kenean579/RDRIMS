<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\University;

class StoreUniversityRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('create', University::class); }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:universities,code',
            'location' => 'nullable|string|max:255',
            'logo_file_id' => 'nullable|exists:files,id',
        ];
    }
}
