<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Patent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatentFileController extends Controller
{
    public function attach(Request $request, Patent $patent): JsonResponse
    {
        $this->authorizeTenantResource($patent, 'update');
        $request->validate(['file_id' => 'required|exists:files,id']);

        $file = File::findOrFail($request->file_id);
        $this->authorizeTenantResource($file, 'view');
        $patent->files()->attach($request->file_id);
        return response()->json(['message' => 'File attached to patent.']);
    }

    public function detach(Patent $patent, int $fileId): JsonResponse
    {
        $this->authorizeTenantResource($patent, 'update');
        $patent->files()->detach($fileId);
        return response()->json(['message' => 'File detached from patent.']);
    }
}
