<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectFileController extends Controller
{
    public function attach(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $request->validate(['file_id' => 'required|exists:files,id']);

        if ($project->files()->where('file_id', $request->file_id)->exists()) {
            return response()->json(['message' => 'File already attached.'], 422);
        }

        $project->files()->attach($request->file_id);

        return response()->json(['message' => 'File attached.']);
    }

    public function detach(Project $project, $fileId): JsonResponse
    {
        $this->authorize('update', $project);

        $project->files()->detach($fileId);

        return response()->json(['message' => 'File detached.']);
    }
}
