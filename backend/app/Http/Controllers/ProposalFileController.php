<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Proposal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProposalFileController extends Controller
{
    public function attach(Request $request, Proposal $proposal): JsonResponse
    {
        // SECURITY FIX: Use proper policy authorization
        $this->authorize('update', $proposal);
        
        $request->validate(['file_id' => 'required|exists:files,id']);

        $file = File::findOrFail($request->file_id);
        
        // SECURITY FIX: Verify file belongs to current user or is accessible
        if ($file->uploaded_by !== $request->user()->id) {
            abort(403, 'You do not have access to this file.');
        }
        
        $proposal->files()->syncWithoutDetaching([$request->file_id]);

        // If main file_id is empty, set this as primary (use explicit assignment)
        if (!$proposal->file_id) {
            $proposal->file_id = $request->file_id;
            $proposal->save();
        }

        return response()->json(['message' => 'File attached to proposal.']);
    }

    public function detach(Proposal $proposal, int $fileId): JsonResponse
    {
        // SECURITY FIX: Use proper policy authorization
        $this->authorize('update', $proposal);
        
        // SECURITY FIX: Verify the file is actually attached to this proposal before detaching
        if (!$proposal->files()->where('file_id', $fileId)->exists()) {
            abort(404, 'File not found in this proposal.');
        }
        
        $proposal->files()->detach($fileId);
        return response()->json(['message' => 'File detached from proposal.']);
    }
}
