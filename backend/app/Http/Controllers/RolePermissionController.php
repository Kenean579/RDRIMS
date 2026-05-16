<?php

namespace App\Http\Controllers;

use App\Http\Requests\SyncRolePermissionsRequest;
use App\Models\Role;
use Illuminate\Http\JsonResponse;

class RolePermissionController extends Controller
{
    public function sync(SyncRolePermissionsRequest $request, Role $role): JsonResponse
    {
        $role->permissions()->sync($request->permissions);
        return response()->json(['message' => 'Permissions synced.']);
    }
}
