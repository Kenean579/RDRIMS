<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProposalFileController extends Controller
{
    public function attach(Request $request, Proposal $proposal): JsonResponse
    {
        $request->validate(['file_id' => 'required|exists:files,id']);
        $proposal->files()->attach($request->file_id);
        return response()->json(['message' => 'File attached to proposal.']);
    }

    public function detach(Proposal $proposal, int $fileId): JsonResponse
    {
        $proposal->files()->detach($fileId);
        return response()->json(['message' => 'File detached from proposal.']);
    }
}
