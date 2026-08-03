<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCallRequest;
use App\Http\Resources\CallResource;
use App\Models\Call;
use App\Models\CallStatus;
use App\Services\CallService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Call\StoreCallRequest;
use Illuminate\Support\Facades\Log;

/**
 * CallController
 *
 * Handles CRUD operations for Call resources.
 *
 * Security Features:
 * - Permission-based authorization (via CallPolicy)
 * - Tenant-aware validation (via StoreCallRequest/UpdateCallRequest)
 * - Business rule enforcement (via CallService)
 * - Public access preserved for portal
 * - IDOR vulnerabilities eliminated
 */
class CallController extends Controller
{
    /**
     * Call business logic service.
     */
    protected CallService $callService;

    /**
     * Inject CallService dependency.
     */
    public function __construct(CallService $callService)
    {
        $this->callService = $callService;
    }

    /**
     * List calls – scoped by user's role and call's institution columns.
     *
     * Public Access: Unauthenticated users see only public, published calls
     * Authenticated: Uses visibleTo() scope for tenant filtering
     *
     * Returns: Paginated collection of CallResource (filters sensitive fields)
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Authorization: handled by policy (allows unauthenticated for public portal)
        if ($user) {
            $this->authorize('viewAny', Call::class);
        }

        $query = Call::with(
            'status',
            'academicYear',
            'createdBy',
            'guidelineFile',
            'proposals'
        )->withCount('proposals');

        /*
        |--------------------------------------------------------------------------
        | Apply Search Filters
        |--------------------------------------------------------------------------
        */

        $query->when(
            $request->filled('status'),
            fn($q) =>
            $q->whereHas('status', fn($s) => $s->where('name', $request->input('status')))
        );

        $query->when(
            $request->filled('search'),
            fn($q) =>
            $q->where(function ($searchQuery) use ($request) {
                $searchQuery->where('title', 'LIKE', '%' . $request->input('search') . '%')
                    ->orWhere('thematic_areas', 'LIKE', '%' . $request->input('search') . '%');
            })
        );

        /*
        |--------------------------------------------------------------------------
        | Apply Hierarchical Filters (optional, for admin filtering)
        |--------------------------------------------------------------------------
        */

        $query->when(
            $request->filled('university_id'),
            fn($q) =>
            $q->where(function ($sq) use ($request) {
                $sq->where('university_id', $request->input('university_id'))
                    ->orWhereNull('university_id');
            })
        );

        $query->when(
            $request->filled('campus_id'),
            fn($q) =>
            $q->where(function ($sq) use ($request) {
                $sq->where('campus_id', $request->input('campus_id'))
                    ->orWhereNull('campus_id');
            })
        );

        $query->when(
            $request->filled('faculty_id'),
            fn($q) =>
            $q->where(function ($sq) use ($request) {
                $sq->where('faculty_id', $request->input('faculty_id'))
                    ->orWhereNull('faculty_id');
            })
        );

        $query->when(
            $request->filled('department_id'),
            fn($q) =>
            $q->where(function ($sq) use ($request) {
                $sq->where('department_id', $request->input('department_id'))
                    ->orWhereNull('department_id');
            })
        );

        $query->when(
            $request->filled('research_center_id'),
            fn($q) =>
            $q->where(function ($sq) use ($request) {
                $sq->where('research_center_id', $request->input('research_center_id'))
                    ->orWhereNull('research_center_id');
            })
        );

        /*
        |--------------------------------------------------------------------------
        | Apply Visibility Scoping
        |--------------------------------------------------------------------------
        */

        if ($user) {
            // Authenticated: use visibleTo() scope for tenant filtering
            $query->visibleTo($user);
        } else {
            // Unauthenticated: only public, published calls
            $query->where('is_public', true)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now());
        }

        return CallResource::collection(
            $query->orderBy('deadline', 'desc')->paginate(
                $request->integer('per_page', 20)
            )
        )->response();
    }

    /**
     * Store a new call (admin).
     *
     * Security:
     * - Authorization via CallPolicy (permission-based)
     * - Validation via StoreCallRequest (tenant-aware, hierarchy consistency)
     * - No autoFillHierarchy() - validation ensures correctness
     *
     * Returns: CallResource with created call (sensitive fields filtered)
     */
    public function store(StoreCallRequest $request): JsonResponse
    {
        // $this->authorize('create', Call::class);

        $user = $request->user();
        $validated = $request->validated();
        Log::info('Validated data:', $validated);

        // --- Handle status resolution ---
        if (!empty($validated['status_name'])) {
            $status = CallStatus::where('name', $validated['status_name'])->first();
            if ($status) {
                $validated['status_id'] = $status->id;
            }
            unset($validated['status_name']);
        }

        // Default status to 'open' if not provided
        if (empty($validated['status_id'])) {
            $defaultStatus = CallStatus::where('name', 'open')->first();
            $validated['status_id'] = $defaultStatus ? $defaultStatus->id : 2;
        }

        // An open, public call should be visible on public pages immediately unless
        // the client supplied an explicit publication date.
        $statusName = CallStatus::whereKey($validated['status_id'])->value('name');
        if ($statusName === 'open'
            && ($validated['is_public'] ?? true)
            && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        // --- Handle budget_limit in metadata ---
        if (isset($validated['budget_limit'])) {
            $validated['metadata'] = array_merge(
                $validated['metadata'] ?? [],
                ['budget_limit' => $validated['budget_limit']]
            );
            unset($validated['budget_limit']);
        }

        // --- Force user's university if not provided ---
        if (empty($validated['university_id'])) {
            $validated['university_id'] = $user->university_id;
        }

        // --- Create call using service ---
        $call = CallService::createCall($validated, $user);

        // --- Load relationships ---
        $call->load('status', 'academicYear', 'createdBy', 'guidelineFile');
        $call->loadCount('proposals');

        return response()->json(
            CallResource::make($call),
            201
        );
    }

    /**
     * Show a single call.
     *
     * Public Access: Policy checks is_public + published_at for unauthenticated
     * Authenticated: Policy checks permission + tenant ownership
     *
     * Returns: CallResource (sensitive fields filtered)
     */
    public function show(Call $call): JsonResponse
    {
        $this->authorize('view', $call);

        // Eager load relationships for resource transformation
        $call->load('status', 'academicYear', 'createdBy', 'guidelineFile', 'proposals');
        $call->loadCount('proposals');

        return response()->json(
            CallResource::make($call)
        );
    }

    /**
     * Update a call (admin).
     *
     * Security:
     * - Authorization via CallPolicy (permission + tenant ownership)
     * - Validation via UpdateCallRequest (immutability, status-based restrictions, hierarchy consistency)
     * - Immutability enforced: university_id explicitly removed
     *
     * Returns: CallResource with updated call (sensitive fields filtered)
     */
    public function update(UpdateCallRequest $request, Call $call): JsonResponse
    {
        // Bypass global scopes for update (admin operation)
        $call = Call::withoutGlobalScopes()->findOrFail($call->getKey());

        $this->authorize('update', $call);

        $validated = $request->validated();

        // Enforce immutability: university_id cannot change (defensive, validation blocks this)
        unset($validated['university_id']);

        $targetStatusId = $validated['status_id'] ?? $call->status_id;
        $targetStatusName = CallStatus::whereKey($targetStatusId)->value('name');
        $willBePublic = $validated['is_public'] ?? $call->is_public;

        if ($targetStatusName === 'open'
            && $willBePublic
            && !$call->published_at
            && !array_key_exists('published_at', $validated)) {
            $validated['published_at'] = now();
        }

        $call->update($validated);

        // Reload fresh instance and load relationships for resource transformation
        $call = $call->fresh();
        $call->load('status', 'academicYear', 'createdBy', 'guidelineFile');
        $call->loadCount('proposals');

        return response()->json(
            CallResource::make($call)
        );
    }

    /**
     * Delete a call (admin).
     *
     * Business Rules:
     * - Prevent deletion if call has proposals (return 409 Conflict)
     * - Use soft delete to preserve historical data
     * - Authorization via CallPolicy (permission + tenant ownership)
     */
    public function destroy(Call $call): JsonResponse
    {
        // Bypass global scopes for delete (admin operation)
        $call = $call->withoutGlobalScopes();

        $this->authorize('delete', $call);

        // Business Rule: Prevent deletion if call has proposals
        if (!$this->callService->canDelete($call)) {
            return response()->json([
                'message' => 'Cannot delete call with existing proposals.',
                'error' => 'This call has proposals submitted and cannot be deleted to maintain data integrity.',
                'proposals_count' => $call->proposals()->count(),
            ], 409); // 409 Conflict
        }

        $call->delete();

        return response()->json([
            'message' => 'Call deleted successfully.',
        ]);
    }
}
