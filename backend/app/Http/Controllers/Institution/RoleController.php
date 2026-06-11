<?php

namespace App\Http\Controllers\Institution;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use App\Models\InstitutionRolePermission;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class RoleController extends Controller
{
    use AuthorizesRequests;

    protected function getUniversityId(Request $request)
    {
        return $request->user()->university_id ?: $request->user()->department?->faculty?->campus?->university_id;
    }

    public function index(Request $request): JsonResponse
    {
        $universityId = $this->getUniversityId($request);
        
        // Return global roles AND this university's specific roles
        $roles = Role::whereNull('university_id')
            ->orWhere('university_id', $universityId)
            ->get();
            
        return response()->json($roles);
    }

    public function store(Request $request): JsonResponse
    {
        $universityId = $this->getUniversityId($request);
        if (!$universityId) abort(403, 'User not associated with a university');

        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string'
        ]);

        // Ensure name is unique within the institution (or global)
        $exists = Role::where('name', $request->name)
            ->where(function($q) use ($universityId) {
                $q->whereNull('university_id')->orWhere('university_id', $universityId);
            })->exists();
            
        if ($exists) {
            return response()->json(['message' => 'Role name already exists'], 422);
        }

        $role = Role::create([
            'name' => $request->name,
            'description' => $request->description,
            'university_id' => $universityId
        ]);

        return response()->json($role, 201);
    }

    public function permissions(Request $request, Role $role): JsonResponse
    {
        $universityId = $this->getUniversityId($request);
        
        // Check if role is accessible (global or belongs to university)
        if ($role->university_id !== null && $role->university_id != $universityId) {
            abort(403);
        }

        // Global base permissions
        $globalPermIds = $role->permissions()->pluck('permissions.id')->toArray();
        
        // Overrides
        $overrides = InstitutionRolePermission::where('university_id', $universityId)
            ->where('role_id', $role->id)
            ->get();
            
        return response()->json([
            'global_permissions' => $globalPermIds,
            'overrides' => $overrides
        ]);
    }

    public function syncOverrides(Request $request, Role $role): JsonResponse
    {
        $universityId = $this->getUniversityId($request);
        if ($role->university_id !== null && $role->university_id != $universityId) {
            abort(403);
        }

        $request->validate([
            'overrides' => 'required|array',
            'overrides.*.permission_id' => 'required|exists:permissions,id',
            'overrides.*.granted' => 'required|boolean'
        ]);

        // Clear existing overrides for this role/uni
        InstitutionRolePermission::where('university_id', $universityId)
            ->where('role_id', $role->id)
            ->delete();

        // Create new ones
        foreach ($request->overrides as $ov) {
            InstitutionRolePermission::create([
                'university_id' => $universityId,
                'role_id' => $role->id,
                'permission_id' => $ov['permission_id'],
                'granted' => $ov['granted']
            ]);
        }

        // Clear cache for all users in this university
        app(PermissionService::class)->clearCacheForUniversity($universityId);

        return response()->json(['message' => 'Overrides synced successfully']);
    }
}
