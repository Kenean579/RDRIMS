<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RoleController extends Controller
{
    use AuthorizesRequests;

    public function index(): JsonResponse
    {
        // Only return global roles (university_id is null)
        return response()->json(Role::whereNull('university_id')->with('permissions')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'description' => 'nullable|string',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'description' => $request->description,
            'university_id' => null, // Always null for global roles
        ]);

        return response()->json($role, 201);
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        if ($role->university_id !== null) {
            return response()->json(['message' => 'Cannot edit institutional roles in global settings'], 403);
        }

        $request->validate([
            'name' => 'required|string|unique:roles,name,'.$role->id,
            'description' => 'nullable|string',
        ]);

        $role->update($request->only('name', 'description'));

        return response()->json($role);
    }

    public function destroy(Role $role): JsonResponse
    {
        if ($role->university_id !== null) {
            return response()->json(['message' => 'Cannot delete institutional roles in global settings'], 403);
        }

        // Potential check: is role assigned to any users?
        if ($role->users()->count() > 0) {
            return response()->json(['message' => 'Cannot delete role assigned to users'], 422);
        }

        $role->delete();

        return response()->json(null, 204);
    }

    public function syncPermissions(Request $request, Role $role): JsonResponse
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->permissions()->sync($request->permissions);

        // Invalidate cache for all users since global permissions changed
        // This is expensive but necessary. A better way would be using cache tags.
        Cache::flush();

        return response()->json($role->load('permissions'));
    }
}
