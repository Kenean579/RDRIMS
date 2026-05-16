<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Department;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('create', Department::class); }

    public function rules(): array
    {
        return [
            'name'       => 'required|string|max:255',
            'code'       => 'required|string|max:50|unique:departments,code',
            'faculty_id' => 'required|exists:faculties,id',
        ];
    }
}
