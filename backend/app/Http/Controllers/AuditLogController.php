<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // Permission check
        if (!auth()->user()->hasRole('super_admin', 'research_admin', 'campus_admin', 'faculty_admin', 'department_head', 'director')) {
            abort(403, 'Unauthorized to view audit logs');
        }

        $user = auth()->user();
        $query = AuditLog::with('user:id,name,email');

        // Hierarchy Scoping
        if (!$user->hasRole('super_admin')) {
            $query->where(function ($q) use ($user) {
                if ($user->hasRole('research_admin')) {
                    $q->where('university_id', $user->university_id);
                } elseif ($user->hasRole('campus_admin')) {
                    $q->where('campus_id', $user->campus_id);
                } elseif ($user->hasRole('faculty_admin')) {
                    $q->where('faculty_id', $user->faculty_id);
                } elseif ($user->hasRole('department_head')) {
                    $q->where('department_id', $user->department_id);
                } elseif ($user->hasRole('director')) {
                    $q->where('research_center_id', $user->research_center_id);
                }
            });
        }

        // Filters
        $logs = $query
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->table_name, fn($q) => $q->where('table_name', $request->table_name))
            ->when($request->action, fn($q) => $q->where('action', $request->action))
            ->when($request->from_date, fn($q) => $q->whereDate('created_at', '>=', $request->from_date))
            ->when($request->to_date, fn($q) => $q->whereDate('created_at', '<=', $request->to_date))
            ->when($request->search_user, fn($q) => $q->whereHas('user', fn($u) => $u->where('name', 'LIKE', '%' . $request->search_user . '%')))
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 50));

        return response()->json($logs);
    }
}
