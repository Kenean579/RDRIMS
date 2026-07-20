<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublicationRequest;
use App\Http\Requests\UpdatePublicationRequest;
use App\Models\Publication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicationController extends Controller
{
    /**
     * List publications – scoped to the authenticated user's institutional hierarchy.
     * Publications are linked to Projects which have a pi_id (principal investigator).
     * We scope via the project's hierarchical() to avoid cross-tenant leakage.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Publication::with('project', 'authors.user', 'researchCenter')
            ->when($request->search, fn($q) => $q->where('title', 'LIKE', '%' . $request->search . '%')
                ->orWhere('journal', 'LIKE', '%' . $request->search . '%'))
            ->when($request->year, fn($q) => $q->whereYear('publication_date', $request->year));

        // Tenant isolation: scope to the user's institution hierarchy via project.pi_id
        if ($user && !$user->hasRole('super_admin')) {
            $query->whereHas('project', function ($pq) use ($user) {
                $pq->hierarchical($user, 'pi_id');
            });
        }

        $publications = $query
            ->orderBy($request->sort ?: 'publication_date', $request->order ?: 'desc')
            ->paginate($request->per_page ?: 20);

        return response()->json($publications);
    }

    public function store(StorePublicationRequest $request): JsonResponse
    {
        $publication = Publication::create($request->validated());
        return response()->json($publication, 201);
    }

    /**
     * Show a single publication – verify the requesting user can access it.
     */
    public function show(Publication $publication): JsonResponse
    {
        $user = request()->user();

        // Non-super-admin users may only view publications that belong to their hierarchy.
        if ($user && !$user->hasRole('super_admin') && $publication->project) {
            $allowed = $publication->project->hierarchical($user, 'pi_id')->where('id', $publication->project_id)->exists();
            if (!$allowed) {
                abort(403, 'You do not have access to this publication.');
            }
        }

        return response()->json($publication->load('project', 'authors.user', 'file'));
    }

    public function update(UpdatePublicationRequest $request, Publication $publication): JsonResponse
    {
        $publication->update($request->validated());
        return response()->json($publication);
    }

    public function destroy(Publication $publication): JsonResponse
    {
        $publication->delete();
        return response()->json(['message' => 'Publication deleted.']);
    }
}
