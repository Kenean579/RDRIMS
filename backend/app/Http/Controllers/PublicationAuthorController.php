<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublicationAuthorRequest;
use App\Http\Requests\UpdatePublicationAuthorRequest;
use App\Models\Publication;
use Illuminate\Http\JsonResponse;

class PublicationAuthorController extends Controller
{
    public function index(Publication $publication): JsonResponse
    {
        return response()->json($publication->authors()->orderBy('author_order')->get());
    }

    public function store(StorePublicationAuthorRequest $request, Publication $publication): JsonResponse
    {
        $author = $publication->authors()->create($request->validated());
        return response()->json($author, 201);
    }

    public function update(UpdatePublicationAuthorRequest $request, Publication $publication, int $authorId): JsonResponse
    {
        $author = $publication->authors()->findOrFail($authorId);
        $author->update($request->validated());
        return response()->json($author);
    }

    public function destroy(Publication $publication, int $authorId): JsonResponse
    {
        $publication->authors()->where('id', $authorId)->delete();
        return response()->json(['message' => 'Author removed.']);
    }
}