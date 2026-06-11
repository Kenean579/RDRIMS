<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublicationRequest;
use App\Http\Requests\UpdatePublicationRequest;
use App\Models\Publication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $publications = Publication::with('project', 'authors.user', 'researchCenter')
            ->when($request->search, fn($q) => $q->where('title', 'LIKE', '%' . $request->search . '%')
                ->orWhere('journal', 'LIKE', '%' . $request->search . '%'))
            ->when($request->year, fn($q) => $q->whereYear('publication_date', $request->year))
            // Apply hierarchical filtering based on research_center_id
            ->when(!$user->hasRole('super_admin'), function ($query) use ($user) {
                if ($user->research_center_id) {
                    $query->where('research_center_id', $user->research_center_id);
                }
                // If user has a project with this publication, they can see it
                $query->orWhereHas('project', function ($q) use ($user) {
                    $q->where('pi_id', $user->id);
                });
            })
            ->orderBy($request->sort ?: 'publication_date', $request->order ?: 'desc')
            ->paginate($request->per_page ?: 20);

        return response()->json($publications);
    }

    public function store(StorePublicationRequest $request): JsonResponse
    {
        $publication = Publication::create($request->validated());
        return response()->json($publication, 201);
    }

    public function show(Publication $publication): JsonResponse
    {
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