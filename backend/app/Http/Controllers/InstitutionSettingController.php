<?php

namespace App\Http\Controllers;

use App\Models\InstitutionSetting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class InstitutionSettingController extends Controller
{
    /**
     * Research & operational settings keys that belong at institution level.
     */
    private array $researchSettingKeys = [
        'max_proposal_budget',
        'min_proposal_budget',
        'ethics_required',
        'plagiarism_threshold',
        'auto_approve_below_budget',
        'default_project_duration_months',
        'max_reviewers_per_proposal',
        'min_reviewers_per_proposal',
        'proposal_review_deadline_days',
        'max_file_upload_size_mb',
        'allowed_file_types',
    ];

    /**
     * Get institution-level settings for the user's hierarchy scope.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $scope = $this->resolveScope($user);

        $settings = InstitutionSetting::where(function ($q) use ($scope) {
            // Match settings at or above the user's level
            if ($scope['research_center_id']) {
                $q->orWhere('research_center_id', $scope['research_center_id']);
            }
            if ($scope['department_id']) {
                $q->orWhere('department_id', $scope['department_id']);
            }
            if ($scope['faculty_id']) {
                $q->orWhere('faculty_id', $scope['faculty_id']);
            }
            if ($scope['campus_id']) {
                $q->orWhere('campus_id', $scope['campus_id']);
            }
            if ($scope['university_id']) {
                $q->orWhere('university_id', $scope['university_id']);
            }
        })->get();

        // Also merge the platform defaults for keys not yet overridden
        $platformDefaults = \App\Models\Setting::whereIn('key', $this->researchSettingKeys)->get();
        $merged = [];
        foreach ($this->researchSettingKeys as $key) {
            $instSetting = $settings->firstWhere('key', $key);
            if ($instSetting) {
                $merged[] = $instSetting;
            } else {
                $default = $platformDefaults->firstWhere('key', $key);
                if ($default) {
                    // Wrap as object-like array for consistent response shape
                    $merged[] = [
                        'id' => null,
                        'key' => $default->key,
                        'value' => $default->value,
                        'description' => $default->description,
                        'university_id' => $scope['university_id'],
                        'campus_id' => $scope['campus_id'],
                        'faculty_id' => $scope['faculty_id'],
                        'department_id' => $scope['department_id'],
                        'research_center_id' => $scope['research_center_id'],
                        'is_default' => true,
                    ];
                }
            }
        }

        return response()->json($merged);
    }

    /**
     * Create or update an institution-level setting.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $scope = $this->resolveScope($user);

        $validated = $request->validate([
            'key' => 'required|string|max:255|in:' . implode(',', $this->researchSettingKeys),
            'value' => 'required|string',
            'description' => 'nullable|string',
        ]);

        // Determine the level at which this setting is being set
        $data = [
            'key' => $validated['key'],
            'value' => $validated['value'],
            'description' => $validated['description'] ?? null,
        ];

        // Only set the scope level the user has authority over
        if ($user->hasRole('super_admin') && $request->filled('university_id')) {
            $data['university_id'] = $request->input('university_id');
        } elseif ($user->hasRole('research_admin')) {
            $data['university_id'] = $scope['university_id'];
        } elseif ($user->hasRole('campus_admin')) {
            $data['campus_id'] = $scope['campus_id'];
        } elseif ($user->hasRole('faculty_admin')) {
            $data['faculty_id'] = $scope['faculty_id'];
        } elseif ($user->hasRole('department_head')) {
            $data['department_id'] = $scope['department_id'];
        } elseif ($user->hasRole('director')) {
            $data['research_center_id'] = $scope['research_center_id'];
        } else {
            throw ValidationException::withMessages(['role' => 'You do not have permission to manage institution settings.']);
        }

        $setting = InstitutionSetting::updateOrCreate(
            [
                'key' => $data['key'],
                'university_id' => $data['university_id'] ?? null,
                'campus_id' => $data['campus_id'] ?? null,
                'faculty_id' => $data['faculty_id'] ?? null,
                'department_id' => $data['department_id'] ?? null,
                'research_center_id' => $data['research_center_id'] ?? null,
            ],
            ['value' => $data['value'], 'description' => $data['description']]
        );

        return response()->json($setting, 201);
    }

    /**
     * Update an existing institution setting.
     */
    public function update(Request $request, InstitutionSetting $institutionSetting): JsonResponse
    {
        $user = $request->user();
        $this->authorizeScope($user, $institutionSetting);

        $validated = $request->validate([
            'value' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $institutionSetting->update($validated);
        return response()->json($institutionSetting);
    }

    /**
     * Delete an institution-level override (reverts to platform default).
     */
    public function destroy(InstitutionSetting $institutionSetting): JsonResponse
    {
        $this->authorizeScope(request()->user(), $institutionSetting);
        $institutionSetting->delete();
        return response()->json(['message' => 'Setting override removed. Platform default will be used.']);
    }

    /**
     * Resolve the user's hierarchy scope.
     */
    private function resolveScope(User $user): array
    {
        $universityId = $user->university_id ?: $user->department?->faculty?->campus?->university_id;
        $campusId = $user->department?->faculty?->campus_id;
        $facultyId = $user->department?->faculty_id;
        $departmentId = $user->department_id;
        $researchCenterId = $user->research_center_id;

        return [
            'university_id' => $universityId,
            'campus_id' => $campusId,
            'faculty_id' => $facultyId,
            'department_id' => $departmentId,
            'research_center_id' => $researchCenterId,
        ];
    }

    /**
     * Validate user has authority over the setting's scope.
     */
    private function authorizeScope(User $user, InstitutionSetting $setting): void
    {
        if ($user->hasRole('super_admin')) {
            return;
        }

        $scope = $this->resolveScope($user);

        if ($setting->university_id && $setting->university_id !== $scope['university_id']) {
            abort(403, 'You can only manage settings for your own institution.');
        }
        if ($setting->campus_id && $setting->campus_id !== $scope['campus_id']) {
            abort(403, 'You can only manage settings for your own campus.');
        }
        if ($setting->faculty_id && $setting->faculty_id !== $scope['faculty_id']) {
            abort(403, 'You can only manage settings for your own faculty.');
        }
        if ($setting->department_id && $setting->department_id !== $scope['department_id']) {
            abort(403, 'You can only manage settings for your own department.');
        }
        if ($setting->research_center_id && $setting->research_center_id !== $scope['research_center_id']) {
            abort(403, 'You can only manage settings for your own research centre.');
        }
    }
}
