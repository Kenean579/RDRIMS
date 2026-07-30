<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\Department;
use Illuminate\Http\JsonResponse;

class DepartmentController extends Controller
{
    /**
     * Display a listing of departments within the authenticated user's university.
     */
    public function index(): JsonResponse
    {
//$this->authorize('viewAny', Department::class);

        $user = auth()->user();

        $query = Department::with(['faculty', 'logoFile']);

        // Filter by user's university (tenant isolation)
        // Department → Faculty → Campus → University
        $query->whereHas('faculty.campus', function ($q) use ($user) {
            $q->where('university_id', $user->university_id);
        });

        return response()->json($query->get());
    }

    /**
     * Store a newly created department.
     */
    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $this->authorize('create', Department::class);

        // The request has already validated faculty_id ownership
        $department = Department::create($request->validated());

        return response()->json(
            $department->load('faculty', 'logoFile'),
            201
        );
    }

    /**
     * Display the specified department.
     */
    public function show(Department $department): JsonResponse
    {
        $this->authorize('view', $department);

        return response()->json(
            $department->load('faculty', 'logoFile', 'users')
        );
    }

    /**
     * Update the specified department.
     */
    public function update(UpdateDepartmentRequest $request, Department $department): JsonResponse
    {
        $this->authorize('update', $department);

        $data = $request->validated();

        // Never allow faculty_id to change (prevents IDOR)
        unset($data['faculty_id']);

        $department->update($data);

        return response()->json(
            $department->fresh()->load('faculty', 'logoFile')
        );
    }

    /**
     * Remove the specified department.
     */
    public function destroy(Department $department): JsonResponse
    {
        $this->authorize('delete', $department);

        // Delete the department; related entities are removed via cascading foreign keys
        $department->delete();

        return response()->json([
            'message' => 'Department deleted successfully.',
        ]);
    }
}
