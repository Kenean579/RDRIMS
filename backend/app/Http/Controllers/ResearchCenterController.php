<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreResearchCenterRequest;
use App\Http\Requests\UpdateResearchCenterRequest;
use App\Models\ResearchCenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResearchCenterController extends Controller
{
    /**
     * List research centers – scoped to the user's institutional hierarchy.
     *
     * Super Admin: all centers platform-wide.
     * research_admin: all centers within their university.
     * campus_admin: centers in their campus.
     * faculty_admin: centers in their faculty.
     * department_head: centers in their department.
     * director: only the centers they manage.
     * researcher/reviewer/student: centers within their university.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = ResearchCenter::with(['director.profileImage', 'university', 'campus', 'faculty', 'logoFile'])
            ->latest();

        if ($user && !$user->hasRole('super_admin')) {
            $userUniversityId = $user->university_id
                ?: $user->department?->faculty?->campus?->university_id;

            if ($user->hasRole('director')) {
                // Directors only see centers they are assigned to
                $centerIds = $user->researchCenters()->pluck('research_centers.id');
                $query->whereIn('id', $centerIds);
            } elseif ($user->hasRole('research_admin', 'finance_officer', 'ethics_officer')) {
                $query->where('university_id', $userUniversityId);
            } elseif ($user->hasRole('campus_admin')) {
                $campusId = $user->department?->faculty?->campus_id;
                $query->where(function ($q) use ($campusId, $userUniversityId) {
                    $q->where('campus_id', $campusId)
                      ->orWhere('university_id', $userUniversityId);
                });
            } elseif ($user->hasRole('faculty_admin')) {
                $facultyId = $user->department?->faculty_id;
                $campusId  = $user->department?->faculty?->campus_id;
                $query->where(function ($q) use ($facultyId, $campusId, $userUniversityId) {
                    $q->where('faculty_id', $facultyId)
                      ->orWhere('campus_id', $campusId)
                      ->orWhere('university_id', $userUniversityId);
                });
            } elseif ($user->hasRole('department_head')) {
                $query->where('university_id', $userUniversityId);
            } else {
                // Researchers, reviewers, students, guests
                $query->where('university_id', $userUniversityId);
            }
        }

        return response()->json($query->paginate(100));
    }

    public function store(StoreResearchCenterRequest $request): JsonResponse
    {
        $this->authorize('create', ResearchCenter::class);
        $center = ResearchCenter::create($request->validated());
        return response()->json($center, 201);
    }

    public function show(ResearchCenter $researchCenter): JsonResponse
    {
        $this->authorizeTenantResource($researchCenter, 'view');
        return response()->json($researchCenter->load('director.profileImage', 'users.profileImage', 'university', 'campus', 'faculty', 'department', 'logoFile'));
    }

    public function update(UpdateResearchCenterRequest $request, ResearchCenter $researchCenter): JsonResponse
    {
        $this->authorizeTenantResource($researchCenter, 'update');
        $researchCenter->update($request->validated());
        return response()->json($researchCenter);
    }

    public function destroy(ResearchCenter $researchCenter): JsonResponse
    {
        $this->authorizeTenantResource($researchCenter, 'delete');
        $researchCenter->delete();
        return response()->json(['message' => 'Research center deleted.']);
    }
}
