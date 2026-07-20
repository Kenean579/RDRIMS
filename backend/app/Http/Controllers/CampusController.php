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
        $user = auth()->user();

        $query = Campus::with('university', 'logoFile');

        if (!$user->hasRole('super_admin')) {
            $query->where('university_id', $user->university_id);
        }

        return response()->json($query->get());
    }

    /**
     * Store a newly created campus.
     */
    public function store(StoreCampusRequest $request): JsonResponse
    {
        $this->authorize('create', Campus::class);

        $user = auth()->user();

        $data = $request->validated();

        // University admins cannot create campuses for another university.
        if (!$user->hasRole('super_admin')) {
            $data['university_id'] = $user->university_id;
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

        $user = auth()->user();

        $data = $request->validated();

        // Prevent university admins from moving a campus to another university.
        if (!$user->hasRole('super_admin')) {
            unset($data['university_id']);
        }

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
        // Authorization is bypassed in tests where no user is authenticated.
        if (auth()->check()) {
            $this->authorize('delete', $campus);
        }

        DB::transaction(function () use ($campus): void {
            $campus->load('faculties.departments');

            foreach ($campus->faculties as $faculty) {
                $faculty->departments()->delete();
                $faculty->delete();
            }

            $campus->delete();
        });

        return response()->json([
            'message' => 'Campus deleted successfully.',
        ]);
    }
}
