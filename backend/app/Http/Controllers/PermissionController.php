<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Http\Requests\PermissionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PermissionController extends Controller
{
    use AuthorizesRequests;

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Permission::class);
        return response()->json(Permission::all());
    }

    public function store(PermissionRequest $request): JsonResponse
    {
        $this->authorize('create', Permission::class);
        $permission = Permission::create($request->validated());
        return response()->json($permission, 201);
    }

    public function show(Permission $permission): JsonResponse
    {
        $this->authorize('view', $permission);
        return response()->json($permission->load('roles'));
    }

    public function update(PermissionRequest $request, Permission $permission): JsonResponse
    {
        $this->authorize('update', $permission);
        $permission->update($request->validated());
        return response()->json($permission);
    }

    public function destroy(Permission $permission): JsonResponse
    {
        $this->authorize('delete', $permission);
        $permission->delete();
        return response()->json(null, 204);
    }
}
