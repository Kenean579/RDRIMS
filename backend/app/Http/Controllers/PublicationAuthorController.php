<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublicationAuthorRequest;
use App\Http\Requests\UpdatePublicationAuthorRequest;
use App\Models\Publication;
use App\Models\PublicationAuthor;
use Illuminate\Http\JsonResponse;

class PublicationAuthorController extends Controller
{
    public function index(Publication $publication): JsonResponse
    {
        $this->authorize('view', $publication);
        $authors = $publication->authors()->orderBy('author_order')->get();
        return response()->json($authors);
    }

    public function store(StorePublicationAuthorRequest $request, Publication $publication): JsonResponse
    {
        $author = $publication->authors()->create($request->validated());
        return response()->json($author, 201);
    }

    public function update(UpdatePublicationAuthorRequest $request, Publication $publication, PublicationAuthor $author): JsonResponse
    {
        $author->update($request->validated());
        return response()->json($author);
    }

    public function destroy(Publication $publication, PublicationAuthor $author): JsonResponse
    {
        $this->authorize('update', $publication);
        $author->delete();
        return response()->json(null, 204);
    }
}