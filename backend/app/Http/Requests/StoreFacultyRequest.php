<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Faculty;

class StoreFacultyRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('create', Faculty::class); }

    public function rules(): array
    {
        return [
            'name'      => 'required|string|max:255',
            'code'      => 'required|string|max:50|unique:faculties,code',
            'campus_id' => 'required|exists:campuses,id',
        ];
    }
}
