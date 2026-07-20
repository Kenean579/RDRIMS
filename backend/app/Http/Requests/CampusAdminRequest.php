<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CampusAdminRequest extends FormRequest
{c
    public function authorize(): bool
    {
        // Only super admins can create campus admins
        return $this->user() && $this->user()->hasRole('super_admin');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'university_id' => 'required|exists:universities,id',
            'campus_id' => 'required|exists:campuses,id',
            // optional role ids – if omitted, default to campus_admin role
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ];
    }
}
