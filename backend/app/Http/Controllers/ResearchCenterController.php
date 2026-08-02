<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreResearchCenterRequest;
use App\Http\Requests\UpdateResearchCenterRequest;
use App\Models\ResearchCenter;
use App\Models\Campus;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\University;
use App\Services\FileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResearchCenterController extends Controller
{
    /**
     * Minimal public center list for forms that accept public submissions.
     */
    public function publicOptions(): JsonResponse
    {
        return response()->json(
            ResearchCenter::query()
                ->with('university:id,name')
                ->orderBy('name')
                ->get(['id', 'name', 'parent_university_id'])
        );
    }

    /**
     * Display a listing of research centers within the authenticated user's university.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ResearchCenter::class);

        $user = $request->user();

        $query = ResearchCenter::with(['director.profileImage', 'university', 'campus', 'faculty', 'department', 'logoFile'])
            ->latest();

        // Filter by user's university (tenant isolation).
        if (! $user->hasRole('super_admin')) {
            $query->where('parent_university_id', $user->resolvedUniversityId());
        }

        return response()->json($query->paginate(100));
    }

    /**
     * Return the hierarchy choices available to the current tenant in one
     * request. This keeps dependent form selects consistent and tenant-safe.
     */
    public function hierarchyOptions(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ResearchCenter::class);

        $universityId = $request->user()->resolvedUniversityId();

        return response()->json([
            'universities' => University::query()
                ->whereKey($universityId)
                ->orderBy('name')
                ->get(['id', 'name']),
            'campuses' => Campus::query()
                ->where('university_id', $universityId)
                ->orderBy('name')
                ->get(['id', 'name', 'university_id']),
            'faculties' => Faculty::query()
                ->whereHas('campus', fn ($query) => $query->where('university_id', $universityId))
                ->orderBy('name')
                ->get(['id', 'name', 'campus_id']),
            'departments' => Department::query()
                ->whereHas('faculty.campus', fn ($query) => $query->where('university_id', $universityId))
                ->orderBy('name')
                ->get(['id', 'name', 'faculty_id']),
        ]);
    }

    /**
     * Store a newly created research center.
     */
    public function store(StoreResearchCenterRequest $request, FileService $fileService): JsonResponse
    {
        $this->authorize('create', ResearchCenter::class);

        $uploadedLogo = null;

        try {
            $researchCenter = DB::transaction(function () use ($request, $fileService, &$uploadedLogo) {
                $data = $request->safe()->except('logo');
                $researchCenter = ResearchCenter::create($data);

                if ($request->hasFile('logo')) {
                    $uploadedLogo = $this->storeLogo($request, $researchCenter, $fileService);
                    $researchCenter->update(['logo_file_id' => $uploadedLogo->id]);
                }

                return $researchCenter;
            });
        } catch (\Throwable $exception) {
            if ($uploadedLogo) {
                $fileService->delete($uploadedLogo);
            }
            throw $exception;
        }

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
       // $this->authorize('view', $researchCenter);

        return response()->json(
            $researchCenter->load('director.profileImage', 'users.profileImage', 'university', 'campus', 'faculty', 'department', 'logoFile')
        );
    }

    /**
     * Update the specified research center.
     */
    public function update(
        UpdateResearchCenterRequest $request,
        ResearchCenter $researchCenter,
        FileService $fileService
    ): JsonResponse
    {
        $this->authorize('update', $researchCenter);

        $data = $request->safe()->except('logo');

        // Never allow hierarchy changes (prevents IDOR)
        unset($data['parent_university_id']);
        unset($data['parent_campus_id']);
        unset($data['parent_faculty_id']);
        unset($data['parent_department_id']);

        $oldLogo = $researchCenter->logoFile;
        $uploadedLogo = null;

        try {
            DB::transaction(function () use ($request, $researchCenter, $fileService, $data, &$uploadedLogo) {
                $researchCenter->update($data);

                if ($request->hasFile('logo')) {
                    $uploadedLogo = $this->storeLogo($request, $researchCenter, $fileService);
                    $researchCenter->update(['logo_file_id' => $uploadedLogo->id]);
                }
            });
        } catch (\Throwable $exception) {
            if ($uploadedLogo) {
                $fileService->delete($uploadedLogo);
            }
            throw $exception;
        }

        if ($uploadedLogo && $oldLogo && $this->isOwnedCenterLogo($oldLogo, $researchCenter)) {
            $fileService->delete($oldLogo);
        }

        return response()->json(
            $researchCenter->fresh()->load('director.profileImage', 'university', 'campus', 'faculty', 'department', 'logoFile')
        );
    }

    private function storeLogo(Request $request, ResearchCenter $researchCenter, FileService $fileService)
    {
        $logo = $fileService->upload($request->file('logo'), $request->user()->id, true);
        $logo->update([
            'metadata' => [
                'purpose' => 'research_center_logo',
                'research_center_id' => $researchCenter->id,
            ],
        ]);

        return $logo;
    }

    private function isOwnedCenterLogo($file, ResearchCenter $researchCenter): bool
    {
        return data_get($file->metadata, 'purpose') === 'research_center_logo'
            && (int) data_get($file->metadata, 'research_center_id') === $researchCenter->id;
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
