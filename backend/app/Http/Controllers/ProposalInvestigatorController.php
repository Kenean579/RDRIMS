<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use App\Models\ProposalInvestigator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProposalInvestigatorController extends Controller
{
    public function index(Proposal $proposal): JsonResponse
    {
        return response()->json($proposal->investigators()->with('user', 'role', 'status')->get());
    }

    public function store(Request $request, Proposal $proposal): JsonResponse
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'name' => 'required_without:user_id|string|max:255',
            'email' => 'required_without:user_id|email|max:255',
            'institution' => 'nullable|string|max:255',
            'role_id' => 'required|exists:investigator_roles,id',
        ]);

        $investigator = $proposal->investigators()->create([
            ...$request->all(),
            'status_id' => 1, // pending
            'invited_at' => now(),
        ]);

        return response()->json($investigator, 201);
    }

    public function destroy(Proposal $proposal, ProposalInvestigator $investigator): JsonResponse
    {
        $investigator->delete();
        return response()->json(['message' => 'Investigator removed.']);
    }
}
