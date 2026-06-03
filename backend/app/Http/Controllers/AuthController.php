<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->userService->register($request->validated());
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $request->authenticate();

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user->load('roles.permissions'),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name'               => 'sometimes|string|max:255',
            'email'              => 'sometimes|email|unique:users,email,' . $user->id,
            'bio'                => 'nullable|string',
            'linkedin_url'       => 'nullable|url',
            'orcid_id'           => 'nullable|string',
            'google_scholar_id'  => 'nullable|string',
            'scopus_id'          => 'nullable|string',
            'profile_image_id'   => 'nullable|exists:files,id',
        ]);
        
        $user->update($validated);
        
        if ($request->has('password') && $request->filled('password')) {
            $request->validate(['password' => 'string|min:8|confirmed']);
            $user->update(['password' => \Hash::make($request->password)]);
        }

        return response()->json($user->load('roles.permissions', 'profile_image'));
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        // In a real app, we would send an email. For now, we'll just return success.
        // Or we can use Laravel's Password broker if we set up mail.
        
        // Mocking success
        return response()->json(['message' => 'Password reset link sent to your email!']);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Simple reset for this demo/local environment
        $user = \App\Models\User::where('email', $request->email)->first();
        if ($user) {
            $user->update(['password' => Hash::make($request->password)]);
            return response()->json(['message' => 'Password has been reset successfully.']);
        }

        return response()->json(['message' => 'Invalid request.'], 400);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json($request->user()->load('roles.permissions', 'department.faculty', 'profile_image'));
    }
}
