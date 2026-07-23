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
     * Display a listing of research centers within the authenticated user's university.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ResearchCenter::class);

        $user = $request->user();

        $query = ResearchCenter::with(['director.profileImage', 'university', 'campus', 'faculty', 'department', 'logoFile'])
            ->latest();

        // Filter by user's university (tenant isolation)
        $query->where('parent_university_id', $user->university_id);

        return response()->json($query->paginate(100));
    }

    /**
     * Store a newly created research center.
     */
    public function store(StoreResearchCenterRequest $request): JsonResponse
    {
        $this->authorize('create', ResearchCenter::class);

        // The request has already validated hierarchy ownership
        $researchCenter = ResearchCenter::create($request->validated());

        return response()->json(
            $researchCenter->load('director.profileImage', 'university', 'campus', 'faculty', 'department', 'logoFile'),
            201
        );
    }

    /**
     * Display the specified research center.
     */
    public function show(ResearchCenter $researchCenter): JsonResponse
    {
        $this->authorize('view', $researchCenter);

        return response()->json(
            $researchCenter->load('director.profileImage', 'users.profileImage', 'university', 'campus', 'faculty', 'department', 'logoFile')
        );
    }

    /**
     * Update the specified research center.
     */
    public function update(UpdateResearchCenterRequest $request, ResearchCenter $researchCenter): JsonResponse
    {
        $this->authorize('update', $researchCenter);

        $data = $request->validated();

        // Never allow hierarchy changes (prevents IDOR)
        unset($data['parent_university_id']);
        unset($data['parent_campus_id']);
        unset($data['parent_faculty_id']);
        unset($data['parent_department_id']);

        $researchCenter->update($data);

        return response()->json(
            $researchCenter->fresh()->load('director.profileImage', 'university', 'campus', 'faculty', 'department', 'logoFile')
        );
    }

    /**
     * Remove the specified research center.
     */
    public function destroy(ResearchCenter $researchCenter): JsonResponse
    {
        $this->authorize('delete', $researchCenter);

        $researchCenter->delete();

        return response()->json([
            'message' => 'Research center deleted successfully.',
        ]);
    }
}
