<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class UserService
{
    public function __construct(private NotificationService $notificationService) {}

    public function register(array $data): User
    {
        // Use provided password or generate a random secure one
        $password = $data['password'] ?? Str::random(16);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($password),
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

        // Generate password reset token for account activation
        $token = Password::broker()->createToken($user);
        
        $frontendUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173'));
        $resetLink = "{$frontendUrl}/reset-password?token={$token}&email=" . urlencode($user->email);

        $this->notificationService->notify($user, 'account_activated',
            "Your account has been created. Please activate your account and set your password using the link below. This link will expire in " . config('auth.passwords.users.expire', 60) . " minutes.",
            ['link' => $resetLink, 'action_text' => 'Activate Account']
        );

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
