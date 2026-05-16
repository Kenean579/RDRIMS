<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;

class LookupController extends Controller
{
    private array $allowedTables = [
        'call_statuses', 'proposal_types', 'proposal_statuses', 'review_decisions',
        'finance_check_statuses', 'ethics_approval_statuses', 'patent_statuses',
        'community_problem_statuses', 'project_statuses', 'milestone_statuses',
        'task_statuses', 'investigator_roles', 'invitation_statuses', 'agreement_types',
        'output_categories', 'student_levels', 'output_subtypes', 'detection_services',
        'detection_statuses', 'participant_types', 'output_statuses', 'center_roles',
    ];

    public function index(string $table): JsonResponse
    {
        if (! in_array($table, $this->allowedTables)) {
            return response()->json(['message' => 'Table not found.'], 404);
        }

        if (! Schema::hasTable($table)) {
            return response()->json(['message' => 'Table does not exist.'], 404);
        }

        $results = \DB::table($table)->orderBy('id')->get(['id', 'name']);

        return response()->json($results);
    }
}
