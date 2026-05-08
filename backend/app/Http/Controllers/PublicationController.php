<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublicationRequest;
use App\Http\Requests\UpdatePublicationRequest;
use App\Models\Publication;
use Illuminate\Http\JsonResponse;

class PublicationController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Publication::class);
        $publications = Publication::with(['project', 'file'])->latest('publication_date')->paginate(20);
        return response()->json($publications);
    }

    public function store(StorePublicationRequest $request): JsonResponse
    {
        $publication = Publication::create($request->validated());
        return response()->json($publication, 201);
    }

    public function show(Publication $publication): JsonResponse
    {
        $this->authorize('view', $publication);
        $publication->load(['project', 'authors.user', 'file']);
        return response()->json($publication);
    }

    public function update(UpdatePublicationRequest $request, Publication $publication): JsonResponse
    {
        $publication->update($request->validated());
        return response()->json($publication);
    }

    public function destroy(Publication $publication): JsonResponse
    {
        $this->authorize('delete', $publication);
        $publication->delete();
        return response()->json(null, 204);
    }
}