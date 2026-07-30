<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFacultyRequest;
use App\Http\Requests\UpdateFacultyRequest;
use App\Models\Faculty;
use Illuminate\Http\JsonResponse;

class FacultyController extends Controller
{
    /**
     * Display a listing of faculties within the authenticated user's university.
     */
    public function index(): JsonResponse
    {
        //c$this->authorize('viewAny', Faculty::class);

        $user = auth()->user();

        $query = Faculty::with(['campus', 'logoFile']);

        // Filter by user's university (tenant isolation)
        // Faculty -> Campus -> University
        $query->whereHas('campus', function ($q) use ($user) {
            $q->where('university_id', $user->university_id);
        });

        return response()->json($query->get());
    }

    /**
     * Store a newly created faculty.
     */
    public function store(StoreFacultyRequest $request): JsonResponse
    {
        $this->authorize('create', Faculty::class);

        // The request has already validated campus_id ownership
        $faculty = Faculty::create($request->validated());

        return response()->json(
            $faculty->load('campus', 'logoFile'),
            201
        );
    }

    /**
     * Display the specified faculty.
     */
    public function show(Faculty $faculty): JsonResponse
    {
        $this->authorize('view', $faculty);

        return response()->json(
            $faculty->load('campus', 'logoFile', 'departments')
        );
    }

    /**
     * Update the specified faculty.
     */
    public function update(UpdateFacultyRequest $request, Faculty $faculty): JsonResponse
    {
        $this->authorize('update', $faculty);

        $data = $request->validated();

        // Never allow campus_id to change (prevents IDOR)
        unset($data['campus_id']);

        $faculty->update($data);

        return response()->json(
            $faculty->fresh()->load('campus', 'logoFile')
        );
    }

    /**
     * Remove the specified faculty.
     */
    public function destroy(Faculty $faculty): JsonResponse
    {
        $this->authorize('delete', $faculty);

        // Delete the faculty; related entities are removed via cascading foreign keys
        $faculty->delete();

        return response()->json([
            'message' => 'Faculty deleted successfully.',
        ]);
    }
}
