<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Output;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OutputFileController extends Controller
{
    public function attach(Request $request, Output $output): JsonResponse
    {
        $this->authorizeTenantResource($output, 'update');
        $request->validate(['file_id' => 'required|exists:files,id']);

        $file = File::findOrFail($request->file_id);
        $this->authorizeTenantResource($file, 'view');
        $output->files()->attach($request->file_id);
        return response()->json(['message' => 'File attached to output.']);
    }

    public function detach(Output $output, int $fileId): JsonResponse
    {
        $this->authorizeTenantResource($output, 'update');
        $output->files()->detach($fileId);
        return response()->json(['message' => 'File detached from output.']);
    }
}
