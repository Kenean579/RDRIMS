<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditLogController extends Controller
{
    /**
     * List audit logs – scoped strictly to the authenticated user's institutional hierarchy.
     *
     * The audit_logs table stores user_id; we derive the institution from the user.
     * This avoids relying on potentially missing campus_id/faculty_id columns on audit_logs.
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();

        // Only admins and directors may view audit logs
        if (!$user->hasRole('super_admin', 'research_admin', 'campus_admin', 'faculty_admin', 'department_head', 'director')) {
            abort(403, 'Unauthorized to view audit logs');
        }

        $query = AuditLog::with('user:id,name,email');

        // Hierarchy Scoping: join through users table to filter by institution
        if (!$user->hasRole('super_admin')) {
            $userUniversityId = $user->university_id
                ?: $user->department?->faculty?->campus?->university_id;

            if ($user->hasRole('research_admin', 'finance_officer', 'ethics_officer')) {
                // University-wide: all users whose department chain leads to this university
                $query->whereHas('user', function ($q) use ($userUniversityId) {
                    $q->where('university_id', $userUniversityId)
                      ->orWhereHas('department.faculty.campus', fn($c) => $c->where('university_id', $userUniversityId));
                });
            } elseif ($user->hasRole('campus_admin')) {
                $campusId = $user->department?->faculty?->campus_id;
                $query->whereHas('user', function ($q) use ($campusId) {
                    $q->whereHas('department.faculty', fn($f) => $f->where('campus_id', $campusId));
                });
            } elseif ($user->hasRole('faculty_admin')) {
                $facultyId = $user->department?->faculty_id;
                $query->whereHas('user', function ($q) use ($facultyId) {
                    $q->whereHas('department', fn($d) => $d->where('faculty_id', $facultyId));
                });
            } elseif ($user->hasRole('department_head')) {
                $deptId = $user->department_id;
                $query->whereHas('user', fn($q) => $q->where('department_id', $deptId));
            } elseif ($user->hasRole('director')) {
                // Director can only see logs from users in their managed research centers
                $centerIds = $user->researchCenters()->pluck('research_centers.id');
                $query->whereHas('user', function ($q) use ($centerIds) {
                    $q->whereIn('research_center_id', $centerIds)
                      ->orWhereHas('researchCenters', fn($rc) => $rc->whereIn('research_centers.id', $centerIds));
                });
            }
        }

        $logs = $query
            ->when($request->user_id,     fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->table_name,  fn($q) => $q->where('table_name', $request->table_name))
            ->when($request->action,      fn($q) => $q->where('action', $request->action))
            ->when($request->from_date,   fn($q) => $q->whereDate('created_at', '>=', $request->from_date))
            ->when($request->to_date,     fn($q) => $q->whereDate('created_at', '<=', $request->to_date))
            ->when($request->search_user, fn($q) => $q->whereHas('user', fn($u) => $u->where('name', 'LIKE', '%' . $request->search_user . '%')))
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 50));

        return response()->json($logs);
    }
}
