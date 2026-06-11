<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    use AuthorizesRequests;

    public function index(): JsonResponse
    {
        return response()->json(Permission::all());
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name',
            'description' => 'nullable|string',
        ]);

        $permission = Permission::create($request->all());

        return response()->json($permission, 201);
    }

    public function update(Request $request, Permission $permission): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name,'.$permission->id,
            'description' => 'nullable|string',
        ]);

        $permission->update($request->all());

        return response()->json($permission);
    }

    public function destroy(Permission $permission): JsonResponse
    {
        if ($permission->roles()->count() > 0) {
            return response()->json(['message' => 'Permission is in use'], 422);
        }
        $permission->delete();

        return response()->json(null, 204);
    }
}
