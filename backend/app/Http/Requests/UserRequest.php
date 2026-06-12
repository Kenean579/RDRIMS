<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\CastBooleanFields;

class UserRequest extends FormRequest
{
    use CastBooleanFields;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->castBooleans(['is_active']);
    }

    public function rules(): array
    {
        $user = $this->route('user');
        $userId = $user instanceof \App\Models\User ? $user->id : $user;

        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . ($userId ?: 'NULL'),
            'password' => $this->isMethod('POST') ? 'required|min:8' : 'nullable|min:8',
            'university_id' => 'nullable|exists:universities,id',
            'department_id' => 'nullable|exists:departments,id',
            'orcid_id' => 'nullable|string|max:255',
            'google_scholar_id' => 'nullable|string|max:255',
            'scopus_id' => 'nullable|string|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'bio' => 'nullable|string',
            'profile_image_id' => 'nullable|exists:files,id',
            'is_active' => 'nullable|boolean',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ];
    }
}
