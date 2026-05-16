<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Role;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Role::class);
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:100|unique:roles,name',
            'description' => 'nullable|string',
        ];
    }
}
