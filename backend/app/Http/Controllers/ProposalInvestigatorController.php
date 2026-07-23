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
        // SECURITY FIX: Add authorization check
        $this->authorize('view', $proposal);
        
        return response()->json($proposal->investigators()->with('user', 'role', 'status')->get());
    }

    public function store(Request $request, Proposal $proposal): JsonResponse
    {
        // SECURITY FIX: Add authorization check - only owner can add investigators
        $this->authorize('update', $proposal);
        
        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'name' => 'required_without:user_id|string|max:255',
            'email' => 'required_without:user_id|email|max:255',
            'institution' => 'nullable|string|max:255',
            'role_id' => 'required|exists:investigator_roles,id',
        ]);

        // SECURITY FIX: Prevent duplicate investigators
        if ($request->user_id && $proposal->investigators()->where('user_id', $request->user_id)->exists()) {
            abort(422, 'This investigator is already added to the proposal.');
        }

        $investigator = $proposal->investigators()->create([
            'user_id' => $request->user_id,
            'name' => $request->name,
            'email' => $request->email,
            'institution' => $request->institution,
            'role_id' => $request->role_id,
            'status_id' => ProposalInvestigator::getStatusId('pending'),
            'invited_at' => now(),
        ]);

        return response()->json($investigator, 201);
    }

    public function destroy(Proposal $proposal, ProposalInvestigator $investigator): JsonResponse
    {
        // SECURITY FIX: Add authorization check - only owner can remove investigators
        $this->authorize('update', $proposal);
        
        // SECURITY FIX: Verify investigator belongs to this proposal
        if ($investigator->proposal_id !== $proposal->id) {
            abort(404, 'Investigator not found in this proposal.');
        }
        
        $investigator->delete();
        return response()->json(['message' => 'Investigator removed.']);
    }
}
