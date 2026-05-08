<?php

namespace App\Http\Controllers;

use App\Models\Output;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OutputFileController extends Controller
{
    public function attach(Request $request, Output $output): JsonResponse
    {
        $this->authorize('update', $output);

        $request->validate(['file_id' => 'required|exists:files,id']);

        if ($output->files()->where('file_id', $request->file_id)->exists()) {
            return response()->json(['message' => 'File already attached.'], 422);
        }

        $output->files()->attach($request->file_id);

        return response()->json(['message' => 'File attached.']);
    }

    public function detach(Output $output, $fileId): JsonResponse
    {
        $this->authorize('update', $output);

        $output->files()->detach($fileId);

        return response()->json(['message' => 'File detached.']);
    }
}