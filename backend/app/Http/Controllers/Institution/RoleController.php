<?php

namespace App\Http\Controllers\Institution;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use App\Models\InstitutionRolePermission;
use App\Models\AuditLog;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class RoleController extends Controller
{
    use AuthorizesRequests;

    protected function getContextHierarchy(Request $request)
    {
        $user = $request->user();
        return [
            'university_id' => $user->university_id ?: $user->department?->faculty?->campus?->university_id,
            'campus_id' => $user->department?->faculty?->campus_id,
            'faculty_id' => $user->department?->faculty_id,
            'department_id' => $user->department_id,
            'research_center_id' => $user->research_center_id
        ];
    }

    public function index(Request $request): JsonResponse
    {
        $ctx = $this->getContextHierarchy($request);
        
        $roles = Role::where(function($q) use ($ctx) {
            $q->whereNull('university_id')
              ->whereNull('campus_id')
              ->whereNull('faculty_id')
              ->whereNull('department_id')
              ->whereNull('research_center_id');
        })
        ->orWhere(function($q) use ($ctx) {
            if ($ctx['university_id']) $q->orWhere('university_id', $ctx['university_id']);
            if ($ctx['campus_id']) $q->orWhere('campus_id', $ctx['campus_id']);
            if ($ctx['faculty_id']) $q->orWhere('faculty_id', $ctx['faculty_id']);
            if ($ctx['department_id']) $q->orWhere('department_id', $ctx['department_id']);
            if ($ctx['research_center_id']) $q->orWhere('research_center_id', $ctx['research_center_id']);
        })
        ->get();
            
        return response()->json($roles);
    }

    public function store(Request $request): JsonResponse
    {
        $ctx = $this->getContextHierarchy($request);

        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'level' => 'required|in:university,campus,faculty,department,research_center'
        ]);

        $scope = [];
        $level = $request->level;
        if ($level === 'university' && $ctx['university_id']) $scope['university_id'] = $ctx['university_id'];
        elseif ($level === 'campus' && $ctx['campus_id']) $scope['campus_id'] = $ctx['campus_id'];
        elseif ($level === 'faculty' && $ctx['faculty_id']) $scope['faculty_id'] = $ctx['faculty_id'];
        elseif ($level === 'department' && $ctx['department_id']) $scope['department_id'] = $ctx['department_id'];
        elseif ($level === 'research_center' && $ctx['research_center_id']) $scope['research_center_id'] = $ctx['research_center_id'];
        
        if (empty($scope)) abort(403, 'User cannot create role at this level');

        // Ensure name is unique
        $exists = Role::where('name', $request->name)->where($scope)->exists();
        if ($exists) return response()->json(['message' => 'Role name already exists at this level'], 422);

        $role = Role::create(array_merge([
            'name' => $request->name,
            'description' => $request->description,
        ], $scope));

        return response()->json($role, 201);
    }

    public function permissions(Request $request, Role $role): JsonResponse
    {
        $ctx = $this->getContextHierarchy($request);
        
        // Global base permissions
        $globalPermIds = $role->permissions()->pluck('permissions.id')->toArray();
        
        // Overrides at user's hierarchy levels
        $overrides = InstitutionRolePermission::where('role_id', $role->id)
            ->where(function($q) use ($ctx) {
                $q->where('university_id', $ctx['university_id'])
                  ->orWhere('campus_id', $ctx['campus_id'])
                  ->orWhere('faculty_id', $ctx['faculty_id'])
                  ->orWhere('department_id', $ctx['department_id'])
                  ->orWhere('research_center_id', $ctx['research_center_id']);
            })->get();
            
        return response()->json([
            'global_permissions' => $globalPermIds,
            'overrides' => $overrides
        ]);
    }

    public function syncOverrides(Request $request, Role $role): JsonResponse
    {
        $ctx = $this->getContextHierarchy($request);
        $request->validate([
            'level' => 'required|in:university,campus,faculty,department,research_center',
            'overrides' => 'required|array',
            'overrides.*.permission_id' => 'required|exists:permissions,id',
            'overrides.*.granted' => 'required|boolean'
        ]);

        $level = $request->level;
        $scope = [];
        if ($level === 'university' && $ctx['university_id']) $scope['university_id'] = $ctx['university_id'];
        elseif ($level === 'campus' && $ctx['campus_id']) $scope['campus_id'] = $ctx['campus_id'];
        elseif ($level === 'faculty' && $ctx['faculty_id']) $scope['faculty_id'] = $ctx['faculty_id'];
        elseif ($level === 'department' && $ctx['department_id']) $scope['department_id'] = $ctx['department_id'];
        elseif ($level === 'research_center' && $ctx['research_center_id']) $scope['research_center_id'] = $ctx['research_center_id'];

        if (empty($scope)) abort(403, 'User cannot sync overrides at this level');

        // Clear existing overrides for this role at this specific level
        $oldOverrides = InstitutionRolePermission::where('role_id', $role->id)->where($scope)->get();
        InstitutionRolePermission::where('role_id', $role->id)->where($scope)->delete();

        // Create new ones
        foreach ($request->overrides as $ov) {
            InstitutionRolePermission::create(array_merge($scope, [
                'role_id' => $role->id,
                'permission_id' => $ov['permission_id'],
                'granted' => $ov['granted']
            ]));
        }

        // Audit Log
        AuditLog::record('sync_overrides', 'institution_role_permissions', $role->id, $oldOverrides->toArray(), [
            'scope' => $scope,
            'overrides' => $request->overrides
        ]);

        // Clear cache
        if ($ctx['university_id']) {
            app(PermissionService::class)->clearCacheForUniversity($ctx['university_id']);
        }

        return response()->json(['message' => 'Overrides synced successfully']);
    }
}
