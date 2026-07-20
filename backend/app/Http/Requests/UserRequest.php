<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\CastBooleanFields;

/**
 * Validates data for admin-managed user creation and updates.
 *
 * Key design decision: `password` is NEVER required here.
 * Admin provisioning (POST) uses UserService::provision() which
 * generates a secure random password internally. The user sets
 * their own password via the email activation link.
 *
 * Self-registration (POST /register) uses a separate RegisterRequest
 * which does require a password.
 */
class UserRequest extends FormRequest
{
    use CastBooleanFields;

    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        if (! $user->isAdmin()) {
            return false;
        }

        $targetUser = $this->route('user');
        if ($targetUser instanceof \App\Models\User) {
            return $user->sharesInstitutionWith($targetUser) || $user->id === $targetUser->id;
        }

        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->castBooleans(['is_active']);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $user = $this->user();

            if (! $user || $user->hasRole('super_admin')) {
                return;
            }

            $tenantUniversityId = $user->resolvedUniversityId();
            if ($this->filled('university_id') && (int) $this->input('university_id') !== (int) $tenantUniversityId) {
                $validator->errors()->add('university_id', 'You can only assign users within your own institution.');
            }

            if ($this->filled('campus_id') && $tenantUniversityId) {
                $campus = \App\Models\Campus::find($this->input('campus_id'));
                if ($campus && (int) $campus->university_id !== (int) $tenantUniversityId) {
                    $validator->errors()->add('campus_id', 'You can only assign users within your own institution.');
                }
            }

            if ($this->filled('faculty_id') && $tenantUniversityId) {
                $faculty = \App\Models\Faculty::find($this->input('faculty_id'));
                $campus = $faculty?->campus;
                if ($faculty && (! $campus || (int) $campus->university_id !== (int) $tenantUniversityId)) {
                    $validator->errors()->add('faculty_id', 'You can only assign users within your own institution.');
                }
            }

            if ($this->filled('department_id') && $tenantUniversityId) {
                $department = \App\Models\Department::find($this->input('department_id'));
                $campus = $department?->faculty?->campus;
                if ($department && (! $campus || (int) $campus->university_id !== (int) $tenantUniversityId)) {
                    $validator->errors()->add('department_id', 'You can only assign users within your own institution.');
                }
            }
        });
    }

    public function rules(): array
    {
        $user   = $this->route('user');
        $userId = $user instanceof \App\Models\User ? $user->id : $user;

        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');

        return [
            'name'               => ($isUpdate ? 'sometimes|' : '') . 'required|string|max:255',
            'email'              => ($isUpdate ? 'sometimes|' : '') . 'required|email|unique:users,email,' . ($userId ?: 'NULL'),

            // Password is NEVER required when an admin creates or updates a user.
            // On creation, UserService::provision() generates a secure random password.
            // The user sets their own password via the activation link.
            // On update, omitting password means the existing password is unchanged.
            'password'           => 'nullable|min:8',

            // Academic hierarchy
            'university_id'      => 'nullable|exists:universities,id',
            'campus_id'          => 'nullable|exists:campuses,id',
            'faculty_id'         => 'nullable|exists:faculties,id',
            'department_id'      => 'nullable|exists:departments,id',
            'research_center_id' => 'nullable|exists:research_centers,id',

            // Professional profile
            'orcid_id'           => 'nullable|string|max:255',
            'google_scholar_id'  => 'nullable|string|max:255',
            'scopus_id'          => 'nullable|string|max:255',
            'linkedin_url'       => 'nullable|url|max:255',
            'bio'                => 'nullable|string',
            'profile_image_id'   => 'nullable|exists:files,id',

            // Account management
            'is_active'          => 'nullable|boolean',

            // Role assignment (admin use)
            'roles'              => 'nullable|array',
            'roles.*'            => 'exists:roles,id',
        ];
    }
}
