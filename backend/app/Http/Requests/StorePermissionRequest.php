<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Permission;

class StorePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Permission::class);
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:100|unique:permissions,name',
            'description' => 'nullable|string',
        ];
    }
}
