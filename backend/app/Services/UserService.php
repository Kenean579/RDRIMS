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

    // ──────────────────────────────────────────────────────────────────────────
    // Workflow 1: External Self-Registration
    // Called by AuthController::register()
    // User provides name + email + password. Account is immediately active.
    // ──────────────────────────────────────────────────────────────────────────

    public function register(array $data): User
    {
        $user = User::create([
            'name'          => $data['name'],
            'email'         => $data['email'],
            'password'      => Hash::make($data['password']),
            'university_id' => $data['university_id'] ?? null,
            'department_id' => $data['department_id'] ?? null,
            'is_active'     => true,
        ]);

        // Assign the default guest role
        $guestRole = Role::where('name', 'guest')->first();
        if ($guestRole) {
            $user->roles()->attach($guestRole->id, [
                'assigned_by' => null,
                'assigned_at' => now(),
            ]);
        }

        // Welcome in-app notification only — no activation email needed
        // because the user already chose their own password.
        $this->notificationService->notify(
            $user,
            'user_registered',
            'Welcome to RDRIMS! Your account has been created successfully.',
            ['link' => config('app.frontend_url', 'http://localhost:5173') . '/app/dashboard', 'action_text' => 'Go to Dashboard']
        );

        return $user->load('roles');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Workflow 2: Admin User Provisioning (Invitation Flow)
    // Called by UserController::store()
    // Administrator creates the account. No password is set or known.
    // The user receives a secure invitation email and sets their own password.
    // ──────────────────────────────────────────────────────────────────────────

    public function provision(array $data): User
    {
        // Generate a cryptographically secure random password.
        // This password is NEVER stored in plaintext, NEVER returned in API responses,
        // and NEVER emailed. The user sets their own password via the activation link.
        $randomPassword = Str::random(32);

        $user = User::create([
            'name'               => $data['name'],
            'email'              => $data['email'],
            'password'           => Hash::make($randomPassword),
            'university_id'      => $data['university_id'] ?? null,
            'department_id'      => $data['department_id'] ?? null,
            'research_center_id' => $data['research_center_id'] ?? null,
            'orcid_id'           => $data['orcid_id'] ?? null,
            'google_scholar_id'  => $data['google_scholar_id'] ?? null,
            'scopus_id'          => $data['scopus_id'] ?? null,
            'linkedin_url'       => $data['linkedin_url'] ?? null,
            'bio'                => $data['bio'] ?? null,
            'is_active'          => true,
            // profile_completed_at is intentionally null until the user
            // completes their profile after activation.
        ]);

        // Assign roles provided by the administrator, or fall back to guest.
        if (!empty($data['roles'])) {
            foreach ($data['roles'] as $roleId) {
                $user->roles()->attach($roleId, [
                    'assigned_by' => null, // set by controller if needed
                    'assigned_at' => now(),
                ]);
            }
        } else {
            $guestRole = Role::where('name', 'guest')->first();
            if ($guestRole) {
                $user->roles()->attach($guestRole->id, [
                    'assigned_by' => null,
                    'assigned_at' => now(),
                ]);
            }
        }

        // Generate a Password Broker token — reusing Laravel's built-in
        // secure token infrastructure (stored hashed in password_reset_tokens).
        $token = Password::broker()->createToken($user);

        $frontendUrl   = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173')), '/');
        $activationUrl = $frontendUrl . '/activate-account?' . http_build_query([
            'token' => $token,
            'email' => $user->email,
        ]);

        // Send professional invitation email.
        // The NotificationService also creates an in-app notification.
        $this->notificationService->sendInvitationEmail($user, $activationUrl);

        return $user->load('roles');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Role Management
    // ──────────────────────────────────────────────────────────────────────────

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

    // ──────────────────────────────────────────────────────────────────────────
    // Account Lifecycle
    // ──────────────────────────────────────────────────────────────────────────

    public function deactivate(User $user): void
    {
        $user->update(['is_active' => false]);
        $user->tokens()->delete();
    }

    public function activate(User $user): void
    {
        $user->update(['is_active' => true]);
    }

    /**
     * Mark a user's profile as complete.
     * Called from AuthController::completeProfile() after the user
     * finishes the post-activation onboarding step.
     */
    public function markProfileComplete(User $user): void
    {
        $user->update(['profile_completed_at' => now()]);
    }
}
