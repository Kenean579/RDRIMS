<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'              => 'required|string|max:255',
            'email'             => 'required|email|unique:users,email',
            'password'          => 'required|string|min:8|confirmed',
            'department_id'     => 'nullable|exists:departments,id',
            'profile_image_id'  => 'nullable|exists:files,id',
            'orcid_id'          => 'nullable|string|max:255',
            'google_scholar_id' => 'nullable|string|max:255',
            'scopus_id'         => 'nullable|string|max:255',
            'linkedin_url'      => 'nullable|url|max:255',
            'is_active'         => 'boolean',
            'bio'               => 'nullable|string',
            'roles'             => 'nullable|array',
            'roles.*'           => 'exists:roles,id',
        ];
    }
}
