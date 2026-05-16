<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Permission;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('permission'));
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:100|unique:permissions,name,' . $this->route('permission')->id,
            'description' => 'nullable|string',
        ];
    }
}
