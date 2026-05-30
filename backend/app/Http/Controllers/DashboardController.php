<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Proposal;
use App\Models\User;
use App\Models\Publication;
use App\Models\ResearchCenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $statusBreakdown = DB::table('proposals')
            ->join('proposal_statuses', 'proposals.status_id', '=', 'proposal_statuses.id')
            ->select('proposal_statuses.name', DB::raw('count(*) as count'))
            ->groupBy('proposal_statuses.id', 'proposal_statuses.name')
            ->get();

        return response()->json([
            'proposals_count' => Proposal::count(),
            'projects_count' => Project::count(),
            'users_count' => User::count(),
            'centers_count' => ResearchCenter::count(),
            'status_breakdown' => $statusBreakdown,
            'recent_proposals' => Proposal::with(['submittedBy', 'status'])->latest()->limit(5)->get(),
            'recent_projects' => Project::with('pi')->latest()->limit(5)->get(),
        ]);
    }

    private function userDashboard(User $user): JsonResponse
    {
        $statusBreakdown = DB::table('proposals')
            ->join('proposal_statuses', 'proposals.status_id', '=', 'proposal_statuses.id')
            ->where('proposals.submitted_by', $user->id)
            ->select('proposal_statuses.name', DB::raw('count(*) as count'))
            ->groupBy('proposal_statuses.id', 'proposal_statuses.name')
            ->get();

        return response()->json([
            'proposals_count' => $user->submittedProposals()->count(),
            'projects_count' => Project::where('pi_id', $user->id)->count(),
            'users_count' => User::count(),
            'centers_count' => ResearchCenter::count(),
            'status_breakdown' => $statusBreakdown,
            'recent_proposals' => $user->submittedProposals()->with('status')->latest()->limit(5)->get(),
            'my_active_projects' => Project::where('pi_id', $user->id)->where('status_id', 1)->latest()->limit(5)->get(),
        ]);
    }
}
