<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCallRequest;
use App\Http\Requests\UpdateCallRequest;
use App\Models\Call;
use Illuminate\Http\JsonResponse;

class CallController extends Controller
{
    public function index(): JsonResponse
    {
        $user = request()->user();
        $calls = Call::with('status', 'academicYear', 'createdBy.profileImage', 'guidelineFile')
            ->when(request('status'), fn($q) => $q->whereHas('status', fn($s) => $s->where('name', request('status'))))
            ->when($user && !$user->hasRole('super_admin'), function ($query) use ($user) {
                $query->where(function ($q) use ($user) {
                    if ($user->hasRole('research_admin') && $user->university_id) {
                        $q->orWhere('university_id', $user->university_id)
                            ->orWhereHas('createdBy', fn($u) => $u->where('university_id', $user->university_id));
                    }
                    if ($user->hasRole('campus_admin') && $user->department_id) {
                        $q->orWhere('campus_id', $user->department->faculty->campus_id);
                    }
                    if ($user->hasRole('faculty_admin') && $user->department_id) {
                        $q->orWhere('faculty_id', $user->department->faculty_id);
                    }
                    if ($user->hasRole('department_head') && $user->department_id) {
                        $q->orWhere('department_id', $user->department_id);
                    }
                    if ($user->hasRole('director')) {
                        $centerIds = $user->research_centers->pluck('id');
                        $q->orWhereIn('research_center_id', $centerIds);
                    }
                });
            })
            ->when(request('university_id'), fn($q) => $q->where('university_id', request('university_id')))
            ->when(request('campus_id'), fn($q) => $q->where('campus_id', request('campus_id')))
            ->when(request('faculty_id'), fn($q) => $q->where('faculty_id', request('faculty_id')))
            ->when(request('department_id'), fn($q) => $q->where('department_id', request('department_id')))
            ->orderBy('deadline', 'desc')
            ->paginate(20);

        return response()->json($calls);
    }

    public function store(StoreCallRequest $request): JsonResponse
    {
        $user = $request->user();
        
        $this->validateScopeForRole($request, $user);
        
        $call = Call::create([
            ...$request->validated(),
            'created_by' => $user->id,
        ]);

        return response()->json($call, 201);
    }

    public function show(Call $call): JsonResponse
    {
        $user = request()->user();
        
        // Allow unauthenticated access for public view of open calls
        if (!$user) {
            return response()->json($call->load('status', 'academicYear', 'guidelineFile', 'proposals'));
        }

        if (!$user->hasRole('super_admin') && !$call->isManageableBy($user)) {
            abort(403, 'You do not have permission to view this call.');
        }
        
        return response()->json($call->load('status', 'academicYear', 'guidelineFile', 'proposals'));
    }

    public function update(UpdateCallRequest $request, Call $call): JsonResponse
    {
        $user = request()->user();
        
        if (!$user || (!$user->hasRole('super_admin') && !$call->isManageableBy($user))) {
            abort(403, 'You do not have permission to update this call.');
        }
        
        $call->update($request->validated());
        return response()->json($call);
    }

    public function destroy(Call $call): JsonResponse
    {
        $user = request()->user();
        
        if (!$user || (!$user->hasRole('super_admin') && !$call->isManageableBy($user))) {
            abort(403, 'You do not have permission to delete this call.');
        }
        
        $call->delete();
        return response()->json(['message' => 'Call deleted.']);
    }
    
    private function validateScopeForRole($request, $user): void
    {
        if ($user->hasRole('super_admin')) {
            return;
        }
        
        if ($user->hasRole('research_admin') && $user->department_id) {
            $userUniversity = $user->department->faculty->campus->university_id;
            if ($request->filled('university_id') && $request->input('university_id') != $userUniversity) {
                abort(403, 'Research admins can only scope to their university or lower levels.');
            }
        }
        
        if ($user->hasRole('campus_admin') && $user->department_id) {
            $userCampus = $user->department->faculty->campus_id;
            if ($request->filled('campus_id') && $request->input('campus_id') != $userCampus) {
                abort(403, 'Campus admins can only scope to their campus or lower levels.');
            }
        }
        
        if ($user->hasRole('faculty_admin') && $user->department_id) {
            $userFaculty = $user->department->faculty_id;
            if ($request->filled('faculty_id') && $request->input('faculty_id') != $userFaculty) {
                abort(403, 'Faculty admins can only scope to their faculty or lower levels.');
            }
        }
        
        if ($user->hasRole('department_head') && $user->department_id) {
            if (!$request->filled('department_id') || $request->input('department_id') != $user->department_id) {
                abort(403, 'Department heads can only scope to their department.');
            }
        }
        
        if ($user->hasRole('director')) {
            if (!$request->filled('research_center_id') || !$user->research_centers->contains($request->input('research_center_id'))) {
                abort(403, 'Directors can only scope to their research centers.');
            }
        }
    }
}