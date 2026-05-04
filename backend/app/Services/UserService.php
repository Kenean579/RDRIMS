<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function register(array $data): User
    {
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $guestRole = Role::where('name', 'guest')->first();
        if ($guestRole) {
            $user->roles()->attach($guestRole, [
                'assigned_by' => null,
                'assigned_at' => now()
            ]);
        }

        return $user;
    }

    public function createToken(User $user): string
    {
        return $user->createToken('auth_token')->plainTextToken;
    }
}
