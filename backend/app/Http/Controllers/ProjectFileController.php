<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProjectFileController extends Controller
{
    use AuthorizesRequests;

    public function attach(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $request->validate(['file_id' => 'required|exists:files,id']);

        $file = File::findOrFail($request->file_id);
        $project->files()->attach($request->file_id);

        return response()->json(['message' => 'File attached to project.']);
    }

    public function detach(Project $project, int $fileId): JsonResponse
    {
        $this->authorize('update', $project);
        $project->files()->detach($fileId);

        return response()->json(['message' => 'File detached from project.']);
    }
}
