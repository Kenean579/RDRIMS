<?php

namespace App\Services;


use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function register(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'university_id' => $data['university_id'] ?? null,
            'department_id' => $data['department_id'] ?? null,
            'is_active' => true,
        ]);

        // Assign default guest role (fetched from DB)
        $guestRole = Role::where('name', 'guest')->first();
        if ($guestRole) {
            $user->roles()->attach($guestRole->id, [
                'assigned_by' => null,
                'assigned_at' => now(),
            ]);
        }

        return $user->load('roles');
    }

    public function assignRole(User $user, Role $role, User $assignedBy): void
    {
        if ($user->roles()->where('role_id', $role->id)->exists()) {
            throw ValidationException::withMessages([
                'role' => 'User already has this role.',
            ]);
        }

        $user->roles()->attach($role->id, [
            'assigned_by' => $assignedBy->id,
            'assigned_at' => now(),
        ]);
    }

    public function revokeRole(User $user, Role $role): void
    {
        $user->roles()->detach($role->id);
    }

    public function deactivate(User $user): void
    {
        $user->update(['is_active' => false]);
        $user->tokens()->delete();
    }

    public function activate(User $user): void
    {
        $user->update(['is_active' => true]);
    }
}
