<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCampusRequest;
use App\Http\Requests\UpdateCampusRequest;
use App\Models\Campus;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CampusController extends Controller
{
    /**
     * Display a listing of campuses.
     */
    public function index(): JsonResponse
    {
        $query = Campus::with('university', 'logoFile');
        return response()->json($query->orderBy('name')->get());
    }

    /**
     * Store a newly created campus.
     */
    public function store(StoreCampusRequest $request): JsonResponse
    {
        $this->authorize('create', Campus::class);

        $user = auth()->user();
        $data = $request->validated();

        // Tenant admins create only within their university. A platform super
        // admin explicitly selects the target university in the request.
        if (! $user->hasRole('super_admin')) {
            $data['university_id'] = $user->resolvedUniversityId();
        }

        $campus = Campus::create($data);

        return response()->json(
            $campus->load('university', 'logoFile'),
            201
        );
    }

    /**
     * Display the specified campus.
     */
    public function show(Campus $campus): JsonResponse
    {
        $this->authorize('view', $campus);

        return response()->json(
            $campus->load('university', 'logoFile', 'faculties')
        );
    }

    /**
     * Update the specified campus.
     */
    public function update(UpdateCampusRequest $request, Campus $campus): JsonResponse
    {
        $this->authorize('update', $campus);
        $data = $request->validated();
        // university_id is never allowed to change; ensure it's removed
        unset($data['university_id']);
        $campus->update($data);
        return response()->json(
            $campus->fresh()->load('university', 'logoFile')
        );
    }

    /**
     * Remove the specified campus.
     */
    public function destroy(Campus $campus): JsonResponse
    {
        // Authorization: only allowed roles can delete; super admin denied by policy
        if (auth()->check()) {
            $this->authorize('delete', $campus);
        }

        // Delete the campus; related entities are removed via cascading foreign keys
        $campus->delete();

        return response()->json([
            'message' => 'Campus deleted successfully.',
        ]);
    }
}
