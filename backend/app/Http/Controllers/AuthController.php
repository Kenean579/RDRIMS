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
use Illuminate\Support\Facades\DB;

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
            'user' => $user->load(
                'roles.permissions',
                'department.faculty.campus.university',
                'university',
                'researchCenter',
                'profileImage',
                'expertise'
            ),
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
            'email'              => 'sometimes|email|unique:users,email,' . $user->getKey(),
            'bio'                => 'nullable|string',
            'linkedin_url'       => 'nullable|url',
            'orcid_id'           => 'nullable|string',
            'google_scholar_id'  => 'nullable|string',
            'scopus_id'          => 'nullable|string',
            'profile_image_id'   => 'nullable|exists:files,id',
            'expertise'          => 'nullable|array',
            'expertise.*'        => 'exists:expertises,id',
            'email_notifications'=> 'sometimes|boolean',
            'email_important'    => 'sometimes|boolean',
            'email_informational'=> 'sometimes|boolean',
        ]);

        $user->update($validated);

        if ($request->has('expertise')) {
            $user->expertise()->sync($request->input('expertise', []));
        }

        if ($request->has('password') && $request->filled('password')) {
            $request->validate(['password' => 'string|min:8|confirmed']);
            $user->update(['password' => Hash::make($request->input('password'))]);
        }

        return response()->json($user->load('roles.permissions', 'profileImage', 'expertise'));
    }

public function forgotPassword(Request $request): JsonResponse
{
    $request->validate([
        'email' => 'required|email|exists:users,email',
    ]);

    $user = \App\Models\User::where('email', $request->email)->first();

    $token = Password::broker()->createToken($user);

    return response()->json([
        'success' => true,
        'message' => 'Password reset token generated successfully.',
        'email' => $user->email,
        'token' => $token,
        'reset_url' => url('/reset-password?token='.$token.'&email='.$user->email),
    ]);
}
public function resetPassword(Request $request): JsonResponse
{
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|string|min:8|confirmed',
    ]);

    $status = Password::reset(
        $request->only(
            'email',
            'password',
            'password_confirmation',
            'token'
        ),
        function ($user, $password) {

            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();
        }
    );

    return $status === Password::PASSWORD_RESET
        ? response()->json([
            'message' => 'Password reset successfully.'
        ])
        : response()->json([
            'message' => __($status)
        ], 400);
}

    public function user(Request $request): JsonResponse
    {
        return response()->json($request->user()->load(
            'roles.permissions',
            'department.faculty.campus.university',
            'university',
            'researchCenter',
            'profileImage',
            'expertise'
        ));
    }
}
