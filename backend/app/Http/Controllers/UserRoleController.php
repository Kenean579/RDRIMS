<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserRoleController extends Controller
{
    use AuthorizesRequests;

    public function index(User $user): JsonResponse
    {
        $this->authorize('view', $user);
        return response()->json($user->roles);
    }

    public function assign(Request $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);
        $request->validate(['role_id' => 'required|exists:roles,id']);
        
        $user->roles()->syncWithoutDetaching([$request->role_id => [
            'assigned_by' => $request->user()->id,
            'assigned_at' => now(),
        ]]);
        
        return response()->json($user->load('roles'));
    }

    public function revoke(User $user, int $roleId): JsonResponse
    {
        $this->authorize('update', $user);
        $user->roles()->detach($roleId);
        return response()->json(null, 204);
    }
}
