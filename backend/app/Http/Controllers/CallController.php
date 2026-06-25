<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCallRequest;
use App\Http\Requests\UpdateCallRequest;
use App\Models\Call;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CallController extends Controller
{
    /**
     * List calls – scoped by user’s role and call’s institution columns.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $calls = Call::with('status', 'academicYear', 'createdBy.profileImage', 'guidelineFile', 'proposals')->withCount('proposals')
            ->when($request->filled('status'), fn($q) =>
                $q->whereHas('status', fn($s) => $s->where('name', $request->input('status')))
            )
            ->when($request->filled('search'), fn($q) =>
                $q->where('title', 'LIKE', '%' . $request->input('search') . '%')
                  ->orWhere('thematic_areas', 'LIKE', '%' . $request->input('search') . '%')
            )
            // Apply hierarchical filters if provided
            // Apply hierarchical filters if provided (include NULL for global visibility)
            ->when($request->filled('university_id'), fn($q) => 
                $q->where(function($sq) use ($request) {
                    $sq->where('university_id', $request->input('university_id'))
                       ->orWhereNull('university_id');
                })
            )
            ->when($request->filled('campus_id'), fn($q) => 
                $q->where(function($sq) use ($request) {
                    $sq->where('campus_id', $request->input('campus_id'))
                       ->orWhereNull('campus_id');
                })
            )
            ->when($request->filled('faculty_id'), fn($q) => 
                $q->where(function($sq) use ($request) {
                    $sq->where('faculty_id', $request->input('faculty_id'))
                       ->orWhereNull('faculty_id');
                })
            )
            ->when($request->filled('department_id'), fn($q) => 
                $q->where(function($sq) use ($request) {
                    $sq->where('department_id', $request->input('department_id'))
                       ->orWhereNull('department_id');
                })
            )
            ->when($request->filled('research_center_id'), fn($q) => 
                $q->where(function($sq) use ($request) {
                    $sq->where('research_center_id', $request->input('research_center_id'))
                       ->orWhereNull('research_center_id');
                })
            )
            // Scoping by user role (if no explicit filter is already applied)
            ->when(!$request->hasAny(['university_id','campus_id','faculty_id','department_id','research_center_id']),
                function ($query) use ($user) {
                    if (!$user || $user->hasRole('super_admin')) {
                        return;
                    }
                    $query->where(function ($q) use ($user) {
                        if ($user->hasRole('research_admin')) {
                            $q->where('university_id', $user->university_id)
                              ->orWhereHas('createdBy', fn($u) => $u->where('university_id', $user->university_id));
                        }
                        if ($user->hasRole('campus_admin')) {
                            $q->orWhere('campus_id', $user->campus_id);
                        }
                        if ($user->hasRole('faculty_admin')) {
                            $q->orWhere('faculty_id', $user->faculty_id);
                        }
                        if ($user->hasRole('department_head')) {
                            $q->orWhere('department_id', $user->department_id);
                        }
                        if ($user->hasRole('director')) {
                            $q->orWhereIn('research_center_id', $user->researchCenters->pluck('id'));
                        }
                        
                        // Default scoping for researchers, reviewers, and students
                        if ($user->hasRole('researcher', 'reviewer', 'student', 'guest')) {
                            $q->orWhereNull('university_id')
                              ->orWhere('university_id', $user->university_id);
                              
                            if ($user->department?->faculty?->campus_id) {
                                $q->orWhere('campus_id', $user->department->faculty->campus_id);
                            }
                            if ($user->department?->faculty_id) {
                                $q->orWhere('faculty_id', $user->department->faculty_id);
                            }
                            if ($user->department_id) {
                                $q->orWhere('department_id', $user->department_id);
                            }
                        }
                    });
                }
            )
            ->orderBy('deadline', 'desc')
            ->paginate(20);

        return response()->json($calls);
    }

    /**
     * Store a new call (admin).
     */
    public function store(StoreCallRequest $request): JsonResponse
    {
        $user = $request->user();
        $this->authorize('create', Call::class);
        $this->validateScopeForRole($request, $user);

        $validated = $request->validated();
        $validated = $this->autoFillHierarchy($validated);
        
        // Ensure a status is set if not provided (non-nullable in DB)
        if (empty($validated['status_id'])) {
            $defaultStatus = \App\Models\CallStatus::where('name', 'open')->first();
            $validated['status_id'] = $defaultStatus ? $defaultStatus->id : 2; // Default to 2 (open)
        }

        // Ensure thematic_areas is not null (required in DB)
        if (empty($validated['thematic_areas'])) {
            $validated['thematic_areas'] = 'General';
        }

        $call = Call::create([
            ...$validated,
            'created_by' => $user->id,
        ]);

        return response()->json($call, 201);
    }

    /**
     * Show a single call.
     */
    public function show(Call $call): JsonResponse
    {
        // Public access is allowed for open calls
        return response()->json($call->load('status', 'academicYear', 'guidelineFile', 'proposals'));
    }

    /**
     * Update a call (admin).
     */
    public function update(UpdateCallRequest $request, Call $call): JsonResponse
    {
        $this->authorize('update', $call);
        $validated = $request->validated();
        $validated = $this->autoFillHierarchy($validated);
        $call->update($validated);
        return response()->json($call);
    }

    /**
     * Delete a call (admin).
     */
    public function destroy(Call $call): JsonResponse
    {
        $this->authorize('delete', $call);
        $call->delete();
        return response()->json(['message' => 'Call deleted']);
    }

    /**
     * Automatically populate parent hierarchy IDs if a child ID is provided.
     */
    private function autoFillHierarchy(array $data): array
    {
        if (!empty($data['department_id'])) {
            $department = \App\Models\Department::with('faculty.campus')->find($data['department_id']);
            if ($department) {
                $data['faculty_id'] = $department->faculty_id;
                $data['campus_id'] = $department->faculty->campus_id ?? null;
                $data['university_id'] = $department->faculty->campus->university_id ?? null;
            }
        } elseif (!empty($data['faculty_id'])) {
            $faculty = \App\Models\Faculty::with('campus')->find($data['faculty_id']);
            if ($faculty) {
                $data['campus_id'] = $faculty->campus_id;
                $data['university_id'] = $faculty->campus->university_id ?? null;
            }
        } elseif (!empty($data['campus_id'])) {
            $campus = \App\Models\Campus::find($data['campus_id']);
            if ($campus) {
                $data['university_id'] = $campus->university_id;
            }
        }
        return $data;
    }

    /**
     * Validate that the admin creating/updating a call doesn't exceed their authority.
     */
    private function validateScopeForRole($request, $user): void
    {
        if ($user->hasRole('super_admin')) {
            return;
        }

        // Helper to extract the user's institution chain
        $userUniversity = $user->university_id ?: $user->department?->faculty?->campus?->university_id;
        $userCampus    = $user->campus_id ?: $user->department?->faculty?->campus_id;
        $userFaculty   = $user->faculty_id ?: $user->department?->faculty_id;
        $userDept      = $user->department_id;

        if ($user->hasRole('research_admin')) {
            if ($request->filled('university_id') && $request->input('university_id') != $userUniversity) {
                abort(403, 'You can only scope calls to your own university.');
            }
        }
        if ($user->hasRole('campus_admin')) {
            if ($request->filled('campus_id') && $request->input('campus_id') != $userCampus) {
                abort(403, 'You can only scope calls to your own campus.');
            }
        }
        if ($user->hasRole('faculty_admin')) {
            if ($request->filled('faculty_id') && $request->input('faculty_id') != $userFaculty) {
                abort(403, 'You can only scope calls to your own faculty.');
            }
        }
        if ($user->hasRole('department_head')) {
            if ($request->filled('department_id') && $request->input('department_id') != $userDept) {
                abort(403, 'You can only scope calls to your own department.');
            }
        }
        if ($user->hasRole('director')) {
            $centerIds = $user->researchCenters->pluck('id');
            if ($request->filled('research_center_id') && !$centerIds->contains($request->input('research_center_id'))) {
                abort(403, 'You can only scope calls to your own research centre.');
            }
        }
    }
}