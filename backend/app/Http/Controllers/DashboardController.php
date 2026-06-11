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
        $projectsQuery = Project::hierarchical($user, 'pi_id');
        $usersQuery = User::query();
        
        if ($request->hasAny(['university_id', 'campus_id', 'faculty_id', 'department_id'])) {
            $usersQuery->join('departments', 'users.department_id', '=', 'departments.id')
                ->join('faculties', 'departments.faculty_id', '=', 'faculties.id')
                ->join('campuses', 'faculties.campus_id', '=', 'campuses.id')
                ->when($request->department_id, fn($q) => $q->where('departments.id', $request->department_id))
                ->when($request->faculty_id, fn($q) => $q->where('faculties.id', $request->faculty_id))
                ->when($request->campus_id, fn($q) => $q->where('campuses.id', $request->campus_id))
                ->when($request->university_id, fn($q) => $q->where('campuses.university_id', $request->university_id))
                ->select('users.*');
        }

        $pubsQuery = Publication::whereHas('project', function($q) use ($user) {
            $q->hierarchical($user, 'pi_id');
        });

        $statusBreakdown = Proposal::hierarchical($user, 'submitted_by')
            ->join('proposal_statuses', 'proposals.status_id', '=', 'proposal_statuses.id')
            ->select('proposal_statuses.name', DB::raw('count(*) as count'))
            ->groupBy('proposal_statuses.id', 'proposal_statuses.name')
            ->get();

        $countsByStatus = $statusBreakdown->pluck('count', 'name');

        $universityStats = University::query()
            ->leftJoin('campuses', 'universities.id', '=', 'campuses.university_id')
            ->leftJoin('faculties', 'campuses.id', '=', 'faculties.campus_id')
            ->leftJoin('departments', 'faculties.id', '=', 'departments.faculty_id')
            ->leftJoin('users', 'departments.id', '=', 'users.department_id')
            ->leftJoin('proposals', 'users.id', '=', 'proposals.submitted_by')
            ->select('universities.name', 'universities.code', DB::raw('count(proposals.id) as proposals_count'))
            ->groupBy('universities.id', 'universities.name', 'universities.code')
            ->get();

        return response()->json([
            'proposals_count' => $proposalsQuery->count(),
            'projects_count' => $projectsQuery->count(),
            'users_count' => $usersQuery->count(),
            'centers_count' => ResearchCenter::count(),
            'universities_count' => University::count(),
            'campuses_count' => Campus::count(),
            'faculties_count' => Faculty::count(),
            'calls_count' => Call::whereHas('status', fn($s) => $s->where('name', 'open'))->count(),
            'publications_count' => $pubsQuery->count(),
            
            // Status-specific counts for the new admin grid
            'completed_count' => ($countsByStatus['approved'] ?? 0) + ($countsByStatus['rejected'] ?? 0),
            'in_progress_count' => ($countsByStatus['under_review'] ?? 0) + ($countsByStatus['revision_requested'] ?? 0),
            'pending_count' => $countsByStatus['submitted'] ?? 0,
            
            'university_stats' => $universityStats,
            'status_breakdown' => $statusBreakdown,
            'recent_proposals' => Proposal::hierarchical($user, 'submitted_by')
                ->with(['submittedBy', 'status'])
                ->latest()->limit(8)->get(),
        ]);
    }

    private function reviewerDashboard(User $user): JsonResponse
    {
        $reviews = DB::table('proposal_reviewers')->where('reviewer_id', $user->getKey())->get();
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
            'recent_proposals' => $user->reviewedProposals()->with(['status', 'submittedBy'])->latest()->limit(8)->get(),
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
            'recent_proposals' => $user->submittedProposals()->with('status')->latest()->limit(8)->get(),
        ]);
    }
}
