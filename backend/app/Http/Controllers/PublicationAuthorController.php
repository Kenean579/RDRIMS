<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublicationAuthorRequest;
use App\Http\Requests\UpdatePublicationAuthorRequest;
use App\Http\Resources\PublicationAuthorResource;
use App\Models\Publication;
use App\Services\PublicationService;
use Illuminate\Http\JsonResponse;

class PublicationAuthorController extends Controller
{
    public function __construct(
        private PublicationService $publicationService,
    ) {}

    public function index(Publication $publication): JsonResponse
    {
        $this->authorize('view', $publication);
        
        $authors = $publication->authors()->with('user')->orderBy('author_order')->get();
        
        return response()->json(PublicationAuthorResource::collection($authors));
    }

    public function store(StorePublicationAuthorRequest $request, Publication $publication): JsonResponse
    {
        $this->authorize('manageAuthors', $publication);
        
        try {
            $this->publicationService->addAuthor(
                $publication,
                $request->validated(),
                $request->user()->id
            );
            
            $authors = $publication->fresh()->authors()->with('user')->orderBy('author_order')->get();
            
            return response()->json(PublicationAuthorResource::collection($authors), 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function update(UpdatePublicationAuthorRequest $request, Publication $publication, int $authorId): JsonResponse
    {
        $this->authorize('manageAuthors', $publication);
        
        $author = $publication->authors()->findOrFail($authorId);
        $author->update($request->validated());
        
        return response()->json(new PublicationAuthorResource($author->load('user')));
    }

    public function destroy(Publication $publication, int $authorId): JsonResponse
    {
        $this->authorize('manageAuthors', $publication);
        
        try {
            $this->publicationService->removeAuthor($publication, $authorId, request()->user()->id);
            return response()->json(['message' => 'Author removed successfully.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
