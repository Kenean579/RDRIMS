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
        $this->authorizeTenantResource($proposal, 'update');
        $request->validate(['file_id' => 'required|exists:files,id']);

        $file = File::findOrFail($request->file_id);
        $this->authorizeTenantResource($file, 'view');
        $proposal->files()->syncWithoutDetaching([$request->file_id]);

        // If main file_id is empty, set this as primary
        if (!$proposal->file_id) {
            $proposal->update(['file_id' => $request->file_id]);
        }

        return response()->json(['message' => 'File attached to proposal.']);
    }

    public function detach(Proposal $proposal, int $fileId): JsonResponse
    {
        $this->authorizeTenantResource($proposal, 'update');
        $proposal->files()->detach($fileId);
        return response()->json(['message' => 'File detached from proposal.']);
    }
}
