<?php

namespace App\Services;

use App\Models\Call;
use App\Models\CallStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Safe\info;

/**
 * CallService
 *
 * Business logic layer for Call operations.
 * Handles status transitions, deletion rules, edit restrictions, and visibility scoping.
 */
class CallService
{

    public static function createCall(array $data, User $user): Call
    {
        DB::beginTransaction();
        try {
            // Set created_by
            $data['created_by'] = $user->id;

            // Set default thematic_areas if empty
            if (empty($data['thematic_areas'])) {
                $data['thematic_areas'] = 'General';
            }

            // Create the call
            $call = Call::create($data);

            // Audit log (optional)
            Log::info('Call created', [
                'call_id' => $call->id,
                'created_by' => $user->id,
                'university_id' => $call->university_id,
            ]);

            DB::commit();
            return $call;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create call: ' . $e->getMessage());
            throw $e;
        }
    }
    /**
     * Check if a call can be deleted.
     *
     * Prevent deletion if the call has proposals to maintain data integrity.
     *
     * @param Call $call
     * @return bool True if deletable, false if has proposals
     */
    public function canDelete(Call $call): bool
    {
        // Prevent deletion if call has any proposals
        return $call->proposals()->count() === 0;
    }

    /**
     * Validate status transition.
     *
     * Business Rules (from user approval):
     * - Draft → Open → Closed (linear progression)
     * - No reopening (Closed → Open not allowed)
     *
     * @param Call $call
     * @param int $newStatusId
     * @return bool True if transition is valid
     */
    public function validateStatusTransition(Call $call, int $newStatusId): bool
    {
        // If status not changing, always valid
        if ($call->status_id === $newStatusId) {
            return true;
        }

        $currentStatus = $call->status?->name;
        $newStatus = CallStatus::find($newStatusId)?->name;

        if (!$currentStatus || !$newStatus) {
            return false; // Invalid status ID
        }

        // Define allowed transitions
        $allowedTransitions = [
            'draft' => ['open'],              // Draft can only go to Open
            'open' => ['closed'],             // Open can only go to Closed
            'closed' => [],                   // Closed is terminal (no reopening)
        ];

        return in_array($newStatus, $allowedTransitions[$currentStatus] ?? [], true);
    }

    /**
     * Check if a call can be edited based on its status.
     *
     * Business Rules (from user approval):
     * - Draft status: All fields editable
     * - Open/Closed status: Restrict editing of workflow-critical fields
     *
     * Immutable fields once Open/Closed:
     * - university_id (always immutable)
     * - campus_id, faculty_id, department_id, research_center_id
     * - deadline (affects proposal submissions)
     * - thematic_areas (affects proposal targeting)
     *
     * Editable fields when Open/Closed:
     * - title, description (clarifications allowed)
     * - is_public, is_featured (visibility changes allowed)
     * - guideline_file_id (document updates allowed)
     * - metadata (flexible data allowed)
     *
     * @param Call $call
     * @param array $fields Fields being edited
     * @return array ['allowed' => bool, 'restricted_fields' => array]
     */
    public function canEdit(Call $call, array $fields): array
    {
        $status = $call->status?->name;

        // Draft status: all fields editable
        if ($status === 'draft') {
            return [
                'allowed' => true,
                'restricted_fields' => [],
            ];
        }

        // Open/Closed status: restrict workflow-critical fields
        if (in_array($status, ['open', 'closed'], true)) {
            $restrictedFields = [
                'university_id',        // Always immutable
                'campus_id',            // Organizational structure
                'faculty_id',           // Organizational structure
                'department_id',        // Organizational structure
                'research_center_id',   // Organizational structure
                'deadline',             // Affects proposal eligibility
                'thematic_areas',       // Affects proposal targeting
                'opens_at',             // Workflow timing
                'closes_at',            // Workflow timing
            ];

            $attemptedRestricted = array_intersect($restrictedFields, array_keys($fields));

            if (!empty($attemptedRestricted)) {
                return [
                    'allowed' => false,
                    'restricted_fields' => array_values($attemptedRestricted),
                ];
            }

            return [
                'allowed' => true,
                'restricted_fields' => [],
            ];
        }

        // Unknown status: allow (fallback)
        return [
            'allowed' => true,
            'restricted_fields' => [],
        ];
    }

    /**
     * Get calls visible to the user.
     *
     * This method extracts the complex visibility logic from the model scope
     * to improve testability and maintainability.
     *
     * Business Rules:
     * - Super Admin: Cannot access tenant calls (policy denies)
     * - Research Admin: University-level calls
     * - Campus Admin: Campus-level calls
     * - Faculty Admin: Faculty-level calls
     * - Department Head: Department-level calls
     * - Director: Research Center calls
     * - Researcher/Reviewer/Student/Guest: Calls matching their hierarchy
     *
     * @param User $user
     * @param Builder $query
     * @return Builder
     */
    public function getVisibleCalls(User $user, Builder $query): Builder
    {
        // Super Admin sees all (but policy will deny, so this shouldn't be reached)
        if ($user->hasRole('super_admin')) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($user) {

            // Global calls (university_id is NULL - though schema doesn't support this)
            $q->whereNull('university_id');

            /*
            |--------------------------------------------------------------------------
            | Research Administration
            |--------------------------------------------------------------------------
            */

            if ($user->hasRole('research_admin')) {
                $q->orWhere(
                    'university_id',
                    $user->resolvedUniversityId()
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Campus Administration
            |--------------------------------------------------------------------------
            */

            if (
                $user->hasRole('campus_admin') &&
                $user->campus_id
            ) {
                $q->orWhere('campus_id', $user->campus_id);
            }

            /*
            |--------------------------------------------------------------------------
            | Faculty Administration
            |--------------------------------------------------------------------------
            */

            if (
                $user->hasRole('faculty_admin') &&
                $user->faculty_id
            ) {
                $q->orWhere('faculty_id', $user->faculty_id);
            }

            /*
            |--------------------------------------------------------------------------
            | Department Administration
            |--------------------------------------------------------------------------
            */

            if (
                $user->hasRole('department_head') &&
                $user->department_id
            ) {
                $q->orWhere(
                    'department_id',
                    $user->department_id
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Research Center Director
            |--------------------------------------------------------------------------
            */

            if ($user->hasRole('director')) {

                $centerIds = $user->researchCenters()
                    ->pluck('research_centers.id');

                if ($centerIds->isNotEmpty()) {
                    $q->orWhereIn(
                        'research_center_id',
                        $centerIds
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Researchers / Reviewers / Students / Guests
            |--------------------------------------------------------------------------
            */

            if (
                $user->hasAnyRole([
                    'researcher',
                    'reviewer',
                    'student',
                    'guest'
                ])
            ) {

                if ($user->resolvedUniversityId()) {
                    $q->orWhere(
                        'university_id',
                        $user->resolvedUniversityId()
                    );
                }

                if ($user->campus_id) {
                    $q->orWhere(
                        'campus_id',
                        $user->campus_id
                    );
                }

                if ($user->faculty_id) {
                    $q->orWhere(
                        'faculty_id',
                        $user->faculty_id
                    );
                }

                if ($user->department_id) {
                    $q->orWhere(
                        'department_id',
                        $user->department_id
                    );
                }
            }
        });
    }
}
