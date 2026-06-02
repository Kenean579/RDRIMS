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

        if ($user->hasRole('super_admin', 'research_admin')) {
            return $this->adminDashboard($user);
        } elseif ($user->hasRole('reviewer')) {
            return $this->reviewerDashboard($user);
        } elseif ($user->hasRole('finance_officer')) {
            return $this->financeDashboard($user);
        } elseif ($user->hasRole('ethics_officer')) {
            return $this->ethicsDashboard($user);
        } elseif ($user->hasRole('department_head')) {
            return $this->departmentHeadDashboard($user);
        } elseif ($user->hasRole('director')) {
            return $this->directorDashboard($user);
        }

        return $this->userDashboard($user);
    }

    private function adminDashboard(User $user): JsonResponse
    {
        $request = request();
        $contextActive = $request->hasAny(['university_id', 'campus_id', 'faculty_id', 'department_id']);

        // Build a base user-ID set scoped to the selected hierarchy
        $userIdsQuery = null;
        if ($contextActive) {
            $userIdsQuery = User::query()
                ->join('departments', 'users.department_id', '=', 'departments.id')
                ->join('faculties', 'departments.faculty_id', '=', 'faculties.id')
                ->join('campuses', 'faculties.campus_id', '=', 'campuses.id')
                ->when($request->department_id, fn($q) => $q->where('departments.id', $request->department_id))
                ->when($request->faculty_id, fn($q) => $q->where('faculties.id', $request->faculty_id))
                ->when($request->campus_id, fn($q) => $q->where('campuses.id', $request->campus_id))
                ->when($request->university_id, fn($q) => $q->where('campuses.university_id', $request->university_id))
                ->pluck('users.id');
        }

        $proposalsQuery = Proposal::query();
        $projectsQuery = Project::query();
        $usersQuery = User::query();
        $pubsQuery = Publication::query();

        if ($userIdsQuery) {
            $proposalsQuery->whereIn('submitted_by', $userIdsQuery);
            $projectsQuery->whereIn('pi_id', $userIdsQuery);
            $usersQuery->whereIn('id', $userIdsQuery);
            // Publications don't have a direct user FK, scope via project
            $pubsQuery->whereHas('project', fn($q) => $q->whereIn('pi_id', $userIdsQuery));
        }

        $statusBreakdown = DB::table('proposals')
            ->join('proposal_statuses', 'proposals.status_id', '=', 'proposal_statuses.id')
            ->when($userIdsQuery, fn($q) => $q->whereIn('proposals.submitted_by', $userIdsQuery))
            ->select('proposal_statuses.name', DB::raw('count(*) as count'))
            ->groupBy('proposal_statuses.id', 'proposal_statuses.name')
            ->get();

        return response()->json([
            'proposals_count' => $proposalsQuery->count(),
            'projects_count' => $projectsQuery->count(),
            'users_count' => $usersQuery->count(),
            'centers_count' => ResearchCenter::count(),
            'publications_count' => $pubsQuery->count(),
            'status_breakdown' => $statusBreakdown,
            'recent_proposals' => Proposal::with(['submittedBy', 'status'])
                ->when($userIdsQuery, fn($q) => $q->whereIn('submitted_by', $userIdsQuery))
                ->latest()->limit(8)->get(),
        ]);
    }

    private function reviewerDashboard(User $user): JsonResponse
    {
        $reviews = DB::table('proposal_reviewers')->where('reviewer_id', $user->id)->get();
        
        $pending = $reviews->whereNull('submitted_at')->count();
        $completed = $reviews->whereNotNull('submitted_at')->count();
        $avgScore = $reviews->whereNotNull('submitted_at')->avg('overall_score') ?? 0;

        return response()->json([
            'pending_reviews' => $pending,
            'completed_reviews' => $completed,
            'average_score' => round($avgScore, 1),
            'status_breakdown' => [
                ['name' => 'Pending', 'count' => $pending],
                ['name' => 'Completed', 'count' => $completed],
            ],
            'recent_proposals' => $user->reviewedProposals()->with('status')->latest()->limit(8)->get(),
        ]);
    }

    private function financeDashboard(User $user): JsonResponse
    {
        // FinanceCheck uses status_id (FK to finance_check_statuses)
        $pendingStatusId = DB::table('finance_check_statuses')->where('name', 'pending')->value('id');
        $approvedStatusId = DB::table('finance_check_statuses')->where('name', 'approved')->value('id');

        $pendingChecks = \App\Models\FinanceCheck::where('status_id', $pendingStatusId)->count();
        $approvedBudgets = \App\Models\FinanceCheck::where('status_id', $approvedStatusId)->count();
        $totalExpenses = \App\Models\Expense::sum('amount');
        $activeProjects = Project::whereHas('status', fn($q) => $q->where('name', 'active'))->count();

        return response()->json([
            'pending_finance_checks' => $pendingChecks,
            'approved_budgets' => $approvedBudgets,
            'total_expenses' => number_format($totalExpenses, 0),
            'active_grants' => $activeProjects,
            'status_breakdown' => [
                ['name' => 'Pending Checks', 'count' => $pendingChecks],
                ['name' => 'Approved Budgets', 'count' => $approvedBudgets],
                ['name' => 'Active Projects', 'count' => $activeProjects],
            ],
            'recent_proposals' => Proposal::with('status')->latest()->limit(8)->get(),
        ]);
    }

    private function ethicsDashboard(User $user): JsonResponse
    {
        // EthicsRequest uses approval_status_id (FK to ethics_approval_statuses)
        $pendingId = DB::table('ethics_approval_statuses')->where('name', 'pending')->value('id');
        $approvedId = DB::table('ethics_approval_statuses')->where('name', 'approved')->value('id');
        $rejectedId = DB::table('ethics_approval_statuses')->where('name', 'rejected')->value('id');

        $pending = \App\Models\EthicsRequest::where('approval_status_id', $pendingId)->count();
        $cleared = \App\Models\EthicsRequest::where('approval_status_id', $approvedId)->count();
        $rejected = \App\Models\EthicsRequest::where('approval_status_id', $rejectedId)->count();

        return response()->json([
            'pending_ethics' => $pending,
            'cleared_ethics' => $cleared,
            'rejected_ethics' => $rejected,
            'total_ethics' => $pending + $cleared + $rejected,
            'status_breakdown' => [
                ['name' => 'Pending', 'count' => $pending],
                ['name' => 'Cleared', 'count' => $cleared],
                ['name' => 'Rejected', 'count' => $rejected],
            ],
            'recent_proposals' => Proposal::with('status')->latest()->limit(8)->get(),
        ]);
    }

    private function departmentHeadDashboard(User $user): JsonResponse
    {
        $deptId = $user->department_id;

        $deptProposals = Proposal::whereHas('submittedBy', fn($q) => $q->where('department_id', $deptId))->count();
        $deptProjects = Project::whereHas('pi', fn($q) => $q->where('department_id', $deptId))->count();
        $deptStaff = User::where('department_id', $deptId)->count();
        $deptPublications = Publication::whereHas('authors', fn($q) => $q->where('department_id', $deptId))->count();

        $statusBreakdown = DB::table('proposals')
            ->join('proposal_statuses', 'proposals.status_id', '=', 'proposal_statuses.id')
            ->join('users', 'proposals.submitted_by', '=', 'users.id')
            ->where('users.department_id', $deptId)
            ->select('proposal_statuses.name', DB::raw('count(*) as count'))
            ->groupBy('proposal_statuses.id', 'proposal_statuses.name')
            ->get();

        return response()->json([
            'proposals_count' => $deptProposals,
            'projects_count' => $deptProjects,
            'staff_count' => $deptStaff,
            'publications_count' => $deptPublications,
            'status_breakdown' => $statusBreakdown,
            'recent_proposals' => Proposal::with(['submittedBy', 'status'])
                ->whereHas('submittedBy', fn($q) => $q->where('department_id', $deptId))
                ->latest()->limit(8)->get(),
        ]);
    }

    private function directorDashboard(User $user): JsonResponse
    {
        $centerIds = $user->researchCenters()->pluck('research_centers.id');
        
        $centerProjects = Project::whereHas('proposal', function($q) use ($centerIds) {
            // projects linked through proposals by researchers in centers
        })->count();

        return response()->json([
            'proposals_count' => Proposal::count(),
            'projects_count' => Project::count(),
            'centers_managed' => $centerIds->count() ?: 1,
            'publications_count' => Publication::count(),
            'status_breakdown' => DB::table('proposals')
                ->join('proposal_statuses', 'proposals.status_id', '=', 'proposal_statuses.id')
                ->select('proposal_statuses.name', DB::raw('count(*) as count'))
                ->groupBy('proposal_statuses.id', 'proposal_statuses.name')
                ->get(),
            'recent_proposals' => Proposal::with(['submittedBy', 'status'])->latest()->limit(8)->get(),
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
            'draft_count' => $user->submittedProposals()->whereHas('status', fn($q) => $q->where('name', 'draft'))->count(),
            'publications_count' => $user->publications()->count(),
            'status_breakdown' => $statusBreakdown,
            'recent_proposals' => $user->submittedProposals()->with('status')->latest()->limit(8)->get(),
        ]);
    }
}
