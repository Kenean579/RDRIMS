<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublicationRequest;
use App\Http\Requests\UpdatePublicationRequest;
use App\Http\Resources\PublicationResource;
use App\Models\Publication;
use App\Services\PublicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicationController extends Controller
{
    public function __construct(
        private PublicationService $publicationService,
    ) {}

    /**
     * List publications – scoped to the authenticated user's institutional hierarchy.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Publication::class);

        $user = $request->user();

        $query = Publication::with('status', 'type', 'project', 'authors.user', 'researchCenter')
            ->when($request->search, fn($q) => $q->where('title', 'LIKE', '%' . $request->search . '%')
                ->orWhere('journal', 'LIKE', '%' . $request->search . '%'))
            ->when($request->year, fn($q) => $q->byYear($request->year))
            ->when($request->type, function ($query, $type) {
                if (filter_var($type, FILTER_VALIDATE_INT) !== false) {
                    $query->where('type_id', (int) $type);
                    return;
                }

                // Backward compatibility for clients that send labels such 
                // as "Book Chapter" instead of the lookup table ID.
                $typeName = strtolower(str_replace([' ', '-'], '_', trim($type)));
                $query->whereHas('type', fn($typeQuery) =>
                    $typeQuery->where('name', $typeName)
                );
            })
            ->when($request->status, fn($q) => $q->where('status_id', $request->status))
            ->when($request->author_id, fn($q) => $q->byAuthor($request->author_id));

        // Tenant isolation: scope to the user's institution hierarchy via project.pi_id
        if ($user && !$user->hasRole('super_admin')) {
            $query->where(function ($visible) use ($user) {
                // Publications may be recorded before they are linked to a
                // project. Keep those visible within the creator's tenant.
                $visible->whereHas('createdBy', function ($creatorQuery) use ($user) {
                    $creatorQuery->where('university_id', $user->resolvedUniversityId());
                })->orWhereHas('project', function ($projectQuery) use ($user) {
                    $projectQuery->hierarchical($user, 'pi_id');
                });
            });
        }

        $publications = $query
            ->orderBy($request->sort ?: 'publication_date', $request->order ?: 'desc')
            ->paginate($request->per_page ?: 20);

        return response()->json([
            'data' => PublicationResource::collection($publications->items()),
            'meta' => [
                'current_page' => $publications->currentPage(),
                'last_page' => $publications->lastPage(),
                'per_page' => $publications->perPage(),
                'total' => $publications->total(),
            ],
        ]);
    }

    /**
     * Store a new publication
     */
    public function store(StorePublicationRequest $request): JsonResponse
    {
        $this->authorize('create', Publication::class);
        
        $publication = $this->publicationService->create(
            $request->validated(),
            $request->user()->id
        );
        
        return response()->json(new PublicationResource($publication->load('status', 'type', 'authors')), 201);
    }

    /**
     * Show a single publication
     */
    public function show(Publication $publication): JsonResponse
    {
        $this->authorize('view', $publication);
        
        $publication->load([
            'status',
            'type',
            'project',
            'authors.user',
            'file',
            'researchCenter',
            'createdBy',
            'verifiedBy',
        ]);
        
        return response()->json(new PublicationResource($publication));
    }

    /**
     * Update a publication
     */
    public function update(UpdatePublicationRequest $request, Publication $publication): JsonResponse
    {
        $this->authorize('update', $publication);
        
        $publication = $this->publicationService->update(
            $publication,
            $request->validated(),
            $request->user()->id
        );
        
        return response()->json(new PublicationResource($publication->load('status', 'type')));
    }

    /**
     * Delete a publication
     */
    public function destroy(Publication $publication): JsonResponse
    {
        $this->authorize('delete', $publication);
        
        $publication->delete();
        
        return response()->json(['message' => 'Publication deleted successfully.']);
    }

    /**
     * Submit publication for review
     */
    public function submit(Publication $publication): JsonResponse
    {
        $this->authorize('submit', $publication);
        
        try {
            $publication = $this->publicationService->submit($publication, request()->user()->id);
            return response()->json(new PublicationResource($publication->load('status')));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Verify a publication
     */
    public function verify(Publication $publication): JsonResponse
    {
        $this->authorize('verify', $publication);
        
        $publication = $this->publicationService->verify($publication, request()->user()->id);
        return response()->json(new PublicationResource($publication->load('verifiedBy')));
    }

    /**
     * Approve a publication
     */
    public function approve(Request $request, Publication $publication): JsonResponse
    {
        $this->authorize('approve', $publication);
        
        $request->validate([
            'comments' => 'nullable|string|max:1000',
        ]);
        
        try {
            $publication = $this->publicationService->approve(
                $publication,
                $request->user()->id,
                $request->comments
            );
            return response()->json(new PublicationResource($publication->load('status')));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Reject a publication
     */
    public function reject(Request $request, Publication $publication): JsonResponse
    {
        $this->authorize('approve', $publication);
        
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);
        
        try {
            $publication = $this->publicationService->reject(
                $publication,
                $request->user()->id,
                $request->reason
            );
            return response()->json(new PublicationResource($publication->load('status')));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Publish a publication
     */
    public function publish(Publication $publication): JsonResponse
    {
        $this->authorize('publish', $publication);
        
        try {
            $publication = $this->publicationService->publish($publication, request()->user()->id);
            return response()->json(new PublicationResource($publication->load('status')));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Update citation count
     */
    public function updateCitations(Request $request, Publication $publication): JsonResponse
    {
        $this->authorize('verify', $publication);
        
        $request->validate([
            'citation_count' => 'required|integer|min:0',
        ]);
        
        $publication = $this->publicationService->updateCitations(
            $publication,
            $request->citation_count,
            $request->user()->id
        );
        
        return response()->json(new PublicationResource($publication));
    }

    /**
     * Get publication statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Publication::class);
        
        $user = $request->user();
        $universityId = null;
        
        // Non-super-admin users get stats for their university only
        if ($user && !$user->hasRole('super_admin')) {
            $universityId = $user->university_id;
        }
        
        $stats = $this->publicationService->getStatistics($universityId);
        
        return response()->json($stats);
    }
}
