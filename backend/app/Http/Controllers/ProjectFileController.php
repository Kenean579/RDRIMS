<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectFileController extends Controller
{
    public function attach(Request $request, Project $project): JsonResponse
    {
        $request->validate(['file_id' => 'required|exists:files,id']);
        $project->files()->attach($request->file_id);
        return response()->json(['message' => 'File attached to project.']);
    }

    public function detach(Project $project, int $fileId): JsonResponse
    {
        $project->files()->detach($fileId);
        return response()->json(['message' => 'File detached from project.']);
    }
}
