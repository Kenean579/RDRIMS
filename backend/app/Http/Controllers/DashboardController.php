<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Proposal;
use App\Models\User;
use App\Models\Publication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return $this->adminDashboard();
        }

        return $this->userDashboard($user);
    }

    private function adminDashboard(): JsonResponse
    {
        return response()->json([
            'stats' => [
                'total_users' => User::count(),
                'total_proposals' => Proposal::count(),
                'total_projects' => Project::count(),
                'total_publications' => Publication::count(),
                'pending_proposals' => Proposal::where('status_id', 2)->count(),
                'active_projects' => Project::where('status_id', 1)->count(),
            ],
            'recent_proposals' => Proposal::with('submittedBy')->latest()->limit(5)->get(),
            'recent_projects' => Project::with('pi')->latest()->limit(5)->get(),
        ]);
    }

    private function userDashboard(User $user): JsonResponse
    {
        return response()->json([
            'stats' => [
                'my_proposals' => $user->submittedProposals()->count(),
                'my_projects' => Project::where('pi_id', $user->id)->count(),
                'my_publications' => $user->publications()->count(),
            ],
            'my_recent_proposals' => $user->submittedProposals()->latest()->limit(5)->get(),
            'my_active_projects' => Project::where('pi_id', $user->id)->where('status_id', 1)->latest()->limit(5)->get(),
        ]);
    }
}
