<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Proposal;
use App\Models\User;
use App\Models\Publication;
use App\Models\ResearchCenter;
use App\Models\University;
use App\Models\Campus;
use App\Models\Faculty;
use App\Models\Call;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // ── PLATFORM LAYER ────────────────────────────────────────────────────────
        // Check for pure super_admin (not holding institutional roles simultaneously)
        $institutionalRoles = [
            'research_admin', 'campus_admin', 'faculty_admin', 'department_head', 
            'director', 'researcher', 'reviewer', 'student', 'finance_officer', 'ethics_officer'
        ];
        
        $hasInstitutionalRole = $user->hasRole(...$institutionalRoles);

        if ($user->hasRole('super_admin') && !$hasInstitutionalRole) {
            return $this->platformDashboard($user);
        }

        // ── INSTITUTIONAL LAYER ───────────────────────────────────────────────────
        if ($user->hasRole('research_admin', 'campus_admin', 'faculty_admin')) {
            return $this->institutionalAdminDashboard($user);
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

    private function platformDashboard(User $user): JsonResponse
    {
        // Super Admin dashboard shows only platform-level data
        return response()->json([
            'universities_count' => University::count(),
            'campuses_count' => Campus::count(),
            'faculties_count' => Faculty::count(),
            'centers_count' => ResearchCenter::count(),
            'users_count' => User::count(),
            'university_stats' => University::withCount('campuses')->get()->map(function($u) {
                return [
                    'name' => $u->name,
                    'code' => $u->code,
                    'campuses_count' => $u->campuses_count
                ];
            }),
            'recent_activity' => \App\Models\AuditLog::latest()->limit(10)->get()
        ]);
    }

    private function institutionalAdminDashboard(User $user): JsonResponse
    {
        $request = request();

        $proposalsQuery = Proposal::hierarchical($user, 'submitted_by');
        $projectsQuery  = Project::hierarchical($user, 'pi_id');
        $usersQuery     = User::query();

        // Resolve user's university for scoping institution-wide counts
        $userUniversityId = $user->university_id
            ?: $user->department?->faculty?->campus?->university_id;

        if ($request->hasAny(['university_id', 'campus_id', 'faculty_id', 'department_id'])) {
            $usersQuery->join('departments', 'users.department_id', '=', 'departments.id')
                ->join('faculties', 'departments.faculty_id', '=', 'faculties.id')
                ->join('campuses', 'faculties.campus_id', '=', 'campuses.id')
                ->when($request->department_id, fn($q) => $q->where('departments.id', $request->department_id))
                ->when($request->faculty_id, fn($q) => $q->where('faculties.id', $request->faculty_id))
                ->when($request->campus_id, fn($q) => $q->where('campuses.id', $request->campus_id))
                ->when($request->university_id, fn($q) => $q->where('campuses.university_id', $request->university_id))
                ->select('users.*');
        } else {
            // Scope users to the authenticated user's institutional hierarchy
            $usersQuery->hierarchical($user, 'id');
        }

        $pubsQuery = Publication::whereHas('project', function ($q) use ($user) {
            $q->hierarchical($user, 'pi_id');
        });

        $statusBreakdown = Proposal::hierarchical($user, 'submitted_by')
            ->join('proposal_statuses', 'proposals.status_id', '=', 'proposal_statuses.id')
            ->select('proposal_statuses.name', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->groupBy('proposal_statuses.id', 'proposal_statuses.name')
            ->get();

        $countsByStatus = $statusBreakdown->pluck('count', 'name');

        $proposalIds = Proposal::hierarchical($user, 'submitted_by')->pluck('id');
        $reviewProgress = app(\App\Services\ReviewService::class)
            ->getInstitutionalReviewProgress($proposalIds);

        // Centers, campuses, faculties scoped to user's university only
        $centersCount    = \App\Models\ResearchCenter::where('parent_university_id', $userUniversityId)->count();
        $campusesCount   = \App\Models\Campus::where('university_id', $userUniversityId)->count();
        $facultiesCount  = \App\Models\Faculty::whereHas('campus', fn($q) => $q->where('university_id', $userUniversityId))->count();

        // University stats: show only the user's own university (not all universities)
        $ownUniversity = \App\Models\University::find($userUniversityId);
        $universityStats = $ownUniversity ? collect([[
            'name'            => $ownUniversity->name,
            'code'            => $ownUniversity->code,
            'proposals_count' => Proposal::hierarchical($user, 'submitted_by')->count(),
        ]]) : collect([]);

        return response()->json([
            'proposals_count'    => $proposalsQuery->count(),
            'projects_count'     => $projectsQuery->count(),
            'users_count'        => $usersQuery->count(),
            'centers_count'      => $centersCount,
            'universities_count' => $ownUniversity ? 1 : 0,
            'campuses_count'     => $campusesCount,
            'faculties_count'    => $facultiesCount,
            'calls_count'        => \App\Models\Call::visibleTo($user)->whereHas('status', fn($s) => $s->where('name', 'open'))->count(),
            'publications_count' => $pubsQuery->count(),

            // Status-specific counts for the new admin grid
            'completed_count'  => ($countsByStatus['approved'] ?? 0) + ($countsByStatus['rejected'] ?? 0),
            'in_progress_count'=> ($countsByStatus['under_review'] ?? 0) + ($countsByStatus['revision_requested'] ?? 0),
            'pending_count'    => $countsByStatus['submitted'] ?? 0,

            'review_progress'  => $reviewProgress,

            'university_stats' => $universityStats,
            'status_breakdown' => $statusBreakdown,
            'monthly_trend'    => Proposal::hierarchical($user, 'submitted_by')
                ->where('created_at', '>=', now()->subMonths(6))
                ->selectRaw("TO_CHAR(created_at, 'Mon') as month, count(*) as count, EXTRACT(MONTH FROM created_at) as month_num")
                ->groupByRaw("DATE_TRUNC('month', created_at), TO_CHAR(created_at, 'Mon'), EXTRACT(MONTH FROM created_at)")
                ->orderByRaw("DATE_TRUNC('month', created_at)")
                ->get(),
            'recent_proposals' => Proposal::hierarchical($user, 'submitted_by')
                ->with(['submittedBy', 'status'])
                ->latest()->limit(8)->get(),
        ]);
    }

    private function reviewerDashboard(User $user): JsonResponse
    {
        $stats = app(\App\Services\ReviewService::class)->getReviewerStats($user);

        return response()->json([
            ...$stats,
            'status_breakdown' => [
                ['name' => 'Pending', 'count' => $stats['pending_reviews']],
                ['name' => 'Completed', 'count' => $stats['completed_reviews']],
            ],
            'recent_proposals' => $user->reviewedProposals()
                ->with(['status'])
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }

    private function financeDashboard(User $user): JsonResponse
    {
        $pendingChecks = \App\Models\FinanceCheck::whereHas('status', fn($q) => $q->where('name', 'pending'))
            ->whereHas('proposal', fn($q) => $q->hierarchical($user, 'submitted_by'))
            ->count();
            
        $approvedBudgets = \App\Models\FinanceCheck::whereHas('status', fn($q) => $q->where('name', 'approved'))
            ->whereHas('proposal', fn($q) => $q->hierarchical($user, 'submitted_by'))
            ->count();
            
        $totalExpenses = \App\Models\Expense::whereHas('project', fn($q) => $q->hierarchical($user, 'pi_id'))->sum('amount');
        
        $activeProjects = Project::hierarchical($user, 'pi_id')
            ->whereHas('status', fn($q) => $q->where('name', 'active'))->count();

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
            'recent_proposals' => Proposal::hierarchical($user, 'submitted_by')
                ->with(['status', 'submittedBy'])
                ->latest()->limit(8)->get(),
        ]);
    }

    private function ethicsDashboard(User $user): JsonResponse
    {
        $pending = \App\Models\EthicsRequest::whereHas('approvalStatus', fn($q) => $q->where('name', 'pending'))
            ->whereHas('proposal', fn($q) => $q->hierarchical($user, 'submitted_by'))
            ->count();
            
        $cleared = \App\Models\EthicsRequest::whereHas('approvalStatus', fn($q) => $q->where('name', 'approved'))
            ->whereHas('proposal', fn($q) => $q->hierarchical($user, 'submitted_by'))
            ->count();
            
        $rejected = \App\Models\EthicsRequest::whereHas('approvalStatus', fn($q) => $q->where('name', 'rejected'))
            ->whereHas('proposal', fn($q) => $q->hierarchical($user, 'submitted_by'))
            ->count();

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
            'recent_proposals' => Proposal::hierarchical($user, 'submitted_by')
                ->with(['status', 'submittedBy'])
                ->latest()->limit(8)->get(),
        ]);
    }

    private function departmentHeadDashboard(User $user): JsonResponse
    {
        $deptId = $user->getAttribute('department_id');
        
        $deptProposals = Proposal::hierarchical($user, 'submitted_by')->count();
        $deptProjects = Project::hierarchical($user, 'pi_id')->count();
        $deptStaff = User::where('department_id', $deptId)->count();
        $deptPublications = Publication::whereHas('project', fn($q) => $q->hierarchical($user, 'pi_id'))->count();

        $statusBreakdown = Proposal::hierarchical($user, 'submitted_by')
            ->join('proposal_statuses', 'proposals.status_id', '=', 'proposal_statuses.id')
            ->select('proposal_statuses.name', DB::raw('count(*) as count'))
            ->groupBy('proposal_statuses.id', 'proposal_statuses.name')
            ->get();

        return response()->json([
            'proposals_count' => $deptProposals,
            'projects_count' => $deptProjects,
            'staff_count' => $deptStaff,
            'publications_count' => $deptPublications,
            'status_breakdown' => $statusBreakdown,
            'recent_proposals' => Proposal::hierarchical($user, 'submitted_by')
                ->with(['submittedBy', 'status'])
                ->latest()->limit(8)->get(),
        ]);
    }

    private function directorDashboard(User $user): JsonResponse
    {
        $centerIds = $user->researchCenters()->pluck('research_centers.id');
        
        $proposalsQuery = Proposal::hierarchical($user, 'submitted_by');
        $projectsQuery = Project::hierarchical($user, 'pi_id');
        $pubsQuery = Publication::whereHas('project', fn($q) => $q->hierarchical($user, 'pi_id'));

        $statusBreakdown = Proposal::hierarchical($user, 'submitted_by')
            ->join('proposal_statuses', 'proposals.status_id', '=', 'proposal_statuses.id')
            ->select('proposal_statuses.name', DB::raw('count(*) as count'))
            ->groupBy('proposal_statuses.id', 'proposal_statuses.name')
            ->get();

        return response()->json([
            'proposals_count' => $proposalsQuery->count(),
            'projects_count' => $projectsQuery->count(),
            'centers_managed' => $centerIds->count(),
            'publications_count' => $pubsQuery->count(),
            'status_breakdown' => $statusBreakdown,
            'recent_proposals' => Proposal::hierarchical($user, 'submitted_by')
                ->with(['submittedBy', 'status'])
                ->latest()->limit(8)->get(),
        ]);
    }

private function userDashboard(User $user): JsonResponse
     {
         $statusBreakdown = DB::table('proposals')
             ->join('proposal_statuses', 'proposals.status_id', '=', 'proposal_statuses.id')
             ->where('proposals.submitted_by', $user->getKey())
             ->select('proposal_statuses.name', DB::raw('count(*) as count'))
             ->groupBy('proposal_statuses.id', 'proposal_statuses.name')
             ->get();

        return response()->json([
'proposals_count' => $user->submittedProposals()->count(),
             'projects_count' => Project::where('pi_id', $user->getKey())->count(),
            'draft_count' => $user->submittedProposals()->whereHas('status', fn($q) => $q->where('name', 'draft'))->count(),
            'publications_count' => $user->publications()->count(),
            'status_breakdown' => $statusBreakdown,
            'monthly_trend' => Proposal::where('submitted_by', $user->getKey())
                ->where('created_at', '>=', now()->subMonths(6))
                ->selectRaw("TO_CHAR(created_at, 'Mon') as month, count(*) as count, EXTRACT(MONTH FROM created_at) as month_num")
                ->groupByRaw("DATE_TRUNC('month', created_at), TO_CHAR(created_at, 'Mon'), EXTRACT(MONTH FROM created_at)")
                ->orderByRaw("DATE_TRUNC('month', created_at)")
                ->get(),
            'recent_proposals' => $user->submittedProposals()->with('status')->latest()->limit(8)->get(),
        ]);
    }
}
