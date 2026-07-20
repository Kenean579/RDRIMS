<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserResearchCenterController extends Controller
{
    use AuthorizesRequests;

    public function index(User $user): JsonResponse
    {
        $this->authorize('view', $user);
        return response()->json($user->researchCenters);
    }

    public function store(Request $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);
        $request->validate(['research_center_id' => 'required|exists:research_centers,id', 'center_role_id' => 'required|integer']);

        $researchCenter = \App\Models\ResearchCenter::findOrFail($request->research_center_id);
        $this->authorize('view', $researchCenter);

        $user->researchCenters()->syncWithoutDetaching([$request->research_center_id => [
            'center_role_id' => $request->center_role_id,
        ]]);

        return response()->json($user->load('researchCenters'));
    }

    public function destroy(User $user, int $centerId): JsonResponse
    {
        $this->authorize('update', $user);
        $user->researchCenters()->detach($centerId);
        return response()->json(null, 204);
    }
}
