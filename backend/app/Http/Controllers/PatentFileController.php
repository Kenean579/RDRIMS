<?php

namespace App\Http\Controllers;

use App\Models\Patent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatentFileController extends Controller
{
    public function attach(Request $request, Patent $patent): JsonResponse
    {
        $this->authorize('update', $patent);

        $request->validate(['file_id' => 'required|exists:files,id']);

        if ($patent->files()->where('file_id', $request->file_id)->exists()) {
            return response()->json(['message' => 'File already attached.'], 422);
        }

        $patent->files()->attach($request->file_id);

        return response()->json(['message' => 'File attached.']);
    }

    public function detach(Patent $patent, $fileId): JsonResponse
    {
        $this->authorize('update', $patent);

        $patent->files()->detach($fileId);

        return response()->json(['message' => 'File detached.']);
    }
}