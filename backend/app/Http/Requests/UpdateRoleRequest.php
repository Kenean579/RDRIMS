<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Role;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('role'));
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:100|unique:roles,name,' . $this->route('role')->id,
            'description' => 'nullable|string',
        ];
    }
}
